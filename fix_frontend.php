<?php
$path = 'resources/views/admin/ai_editor/index.blade.php';
$content = file_get_contents($path);

// 1. Replace the input url with textarea urls
$searchInput = '<input type="url" x-model="url" :required="editorMode === \'manual\'" placeholder="https://example.com/lp" class="w-full pl-10 pr-4 py-3 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white transition-colors">';
$replaceInput = '<textarea x-model="urls" :required="editorMode === \'manual\'" rows="4" placeholder="https://example.com/lp1&#10;https://example.com/lp2&#10;(Maksimal 10 URL, satu URL per baris)" class="w-full pl-10 pr-4 py-3 rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white transition-colors leading-relaxed"></textarea>';
$content = str_replace($searchInput, $replaceInput, $content);

// Fix label
$content = str_replace('<label class="block text-sm font-semibold text-slate-700 mb-2">URL Target (LP / AMP)</label>', '<label class="block text-sm font-semibold text-slate-700 mb-2">URL Target (LP / AMP) - Maks 10</label>', $content);

// Align icon to top instead of center for textarea
$searchIcon = '<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">';
$replaceIcon = '<div class="absolute top-0 left-0 pt-3.5 pl-3 flex items-start pointer-events-none">';
$content = str_replace($searchIcon, $replaceIcon, $content);

// 2. Add progress bar before the "Area Kerja Editor" div
$searchAreaKerja = '<div class="relative bg-white/50 backdrop-blur-sm rounded-3xl p-6 border border-slate-200/60 shadow-xl overflow-hidden flex flex-col items-center justify-center min-h-[400px] lg:min-h-[600px]">';
$progressBarHTML = '
                    <!-- Progress Bar (Batch Processing) -->
                    <div x-show="progress.active" style="display: none;" class="w-full max-w-xl mx-auto mb-6 p-5 bg-white rounded-2xl shadow-lg border border-indigo-100">
                        <div class="flex justify-between items-end mb-2">
                            <div>
                                <h4 class="font-bold text-slate-800">Sedang Memproses...</h4>
                                <p class="text-xs text-slate-500 mt-1" x-text="progress.statusText"></p>
                            </div>
                            <span class="text-sm font-black text-indigo-600" x-text="progress.percentage + \'%\'"></span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-500 to-sky-400 h-3 rounded-full transition-all duration-300" :style="\'width: \' + progress.percentage + \'%\'"></div>
                        </div>
                    </div>
';
$content = str_replace($searchAreaKerja, $progressBarHTML . "\n" . $searchAreaKerja, $content);


// 3. Update Alpine logic - Use precise regex
$content = preg_replace('/(\s+url: \'\',)(\s+prompt: \'\',)/', "$1 urls: '', progress: { current: 0, total: 0, percentage: 0, active: false, statusText: '' },$2", $content);

// 4. Update processForm method
$searchProcessForm = "
                  async processForm() {
                      if (!this.url || !this.prompt) return;
                      this.loading = true;
                      this.error = '';
                      this.activeTab = 'html';
                      
                      try {
                          const res = await fetch('{{ route('admin.ai_editor.process') }}', {
                              method: 'POST',
                              headers: {
                                  'Content-Type': 'application/json',
                                  'X-CSRF-TOKEN': '{{ csrf_token() }}'
                              },
                              body: JSON.stringify({
                                  url: this.url,
                                  prompt: this.prompt
                              })
                          });
                          
                          const data = await res.json();
                          if (!res.ok) throw new Error(data.error || 'Terjadi kesalahan saat memproses data.');
                          
                          this.originalHtml = data.original_html;
                          this.modifiedHtml = data.modified_html;
                          this.operations = data.operations;
                          this.initDiff();
                      } catch (err) {
                          this.error = err.message;
                      } finally {
                          this.loading = false;
                      }
                  },
";

$newProcessForm = "
                  async processForm() {
                      if (!this.urls || !this.prompt) return;
                      
                      let targetUrls = this.urls.split('\\n').map(u => u.trim()).filter(u => u !== '');
                      if (targetUrls.length === 0) return;
                      if (targetUrls.length > 10) {
                          alert('Maksimal hanya 10 URL sekaligus!');
                          return;
                      }

                      this.loading = true;
                      this.error = '';
                      this.activeTab = 'html';
                      
                      let batchResults = [];
                      this.progress.active = true;
                      this.progress.total = targetUrls.length;
                      this.progress.current = 0;
                      this.progress.percentage = 0;
                      
                      try {
                          for (let i = 0; i < targetUrls.length; i++) {
                              let currentUrl = targetUrls[i];
                              this.progress.statusText = 'Memproses URL ' + (i+1) + ' dari ' + targetUrls.length + '...';
                              
                              const res = await fetch('{{ route('admin.ai_editor.process') }}', {
                                  method: 'POST',
                                  headers: {
                                      'Content-Type': 'application/json',
                                      'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                  },
                                  body: JSON.stringify({
                                      url: currentUrl,
                                      prompt: this.prompt
                                  })
                              });
                              
                              const data = await res.json();
                              if (!res.ok) throw new Error(data.error || 'Gagal memproses ' + currentUrl);
                              
                              batchResults.push({
                                  url: currentUrl,
                                  html: data.modified_html
                              });
                              
                              this.progress.current = i + 1;
                              this.progress.percentage = Math.round((this.progress.current / this.progress.total) * 100);
                              
                              // If only 1 URL, show preview diff
                              if (targetUrls.length === 1) {
                                  this.originalHtml = data.original_html;
                                  this.modifiedHtml = data.modified_html;
                                  this.operations = data.operations;
                                  this.initDiff();
                              }
                          }
                          
                          this.progress.statusText = 'Selesai! Menyiapkan file ZIP...';
                          
                          // Download ZIP logic using hidden form post
                          if (batchResults.length > 0) {
                              let form = document.createElement('form');
                              form.method = 'POST';
                              form.action = '{{ route('admin.ai_editor.download_batch') }}';
                              
                              let csrfInput = document.createElement('input');
                              csrfInput.type = 'hidden';
                              csrfInput.name = '_token';
                              csrfInput.value = '{{ csrf_token() }}';
                              form.appendChild(csrfInput);
                              
                              let dataInput = document.createElement('input');
                              dataInput.type = 'hidden';
                              dataInput.name = 'batch_data';
                              dataInput.value = JSON.stringify(batchResults);
                              form.appendChild(dataInput);
                              
                              document.body.appendChild(form);
                              form.submit();
                              document.body.removeChild(form);
                          }

                      } catch (err) {
                          this.error = err.message;
                      } finally {
                          this.loading = false;
                          setTimeout(() => { this.progress.active = false; }, 3000);
                      }
                  },
";

$content = str_replace($searchProcessForm, $newProcessForm, $content);

file_put_contents($path, $content);
echo "Updated Alpine component safely for batch processing";
