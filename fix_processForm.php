<?php
$path = 'resources/views/admin/ai_editor/index.blade.php';
$content = file_get_contents($path);

$searchProcessForm = '/\s*async processForm\(\)\s*\{.*?(?=async processAutoForm\(\))/s';

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
                    this.errorMsg = '';
                    this.assembledPrompt = '';
                    this.hasResult = false;
                    
                    let batchResults = [];
                    this.progress.active = true;
                    this.progress.total = targetUrls.length;
                    this.progress.current = 0;
                    this.progress.percentage = 0;
                    
                    try {
                        for (let i = 0; i < targetUrls.length; i++) {
                            let currentUrl = targetUrls[i];
                            this.progress.statusText = 'Memproses URL ' + (i+1) + ' dari ' + targetUrls.length + '... (' + currentUrl.substring(0, 30) + '...)';
                            
                            const response = await fetch('/admin/ai-editor/process', {
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
                            
                            const data = await response.json();
                            
                            if (!response.ok) {
                                throw new Error(data.error || 'Gagal memproses ' + currentUrl);
                            }
                            
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
                                this.hasResult = true;
                                this.activeTab = 'preview';
                            }
                        }
                        
                        this.progress.statusText = 'Selesai! Menyiapkan file ZIP...';
                        
                        // Download ZIP logic using hidden form post
                        if (batchResults.length > 0) {
                            let form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '{{ route(\'admin.ai_editor.download_batch\') }}';
                            
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

                    } catch (error) {
                        this.errorMsg = error.message;
                    } finally {
                        this.loading = false;
                        setTimeout(() => { this.progress.active = false; }, 3000);
                    }
                },
                
                ";

$content = preg_replace($searchProcessForm, $newProcessForm, $content);

file_put_contents($path, $content);
echo "Replaced processForm successfully";
