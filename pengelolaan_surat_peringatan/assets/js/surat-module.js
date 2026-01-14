// Surat Module
class SuratModule {
    constructor() {
        this.suratModal = new bootstrap.Modal(document.getElementById('suratModal'));
        this.templateModal = new bootstrap.Modal(document.getElementById('templateModal'));
        this.previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    }
    
    showSuratModal(suratId = null) {
        const form = document.getElementById('formSuratPeringatan');
        form.reset();
        form.classList.remove('was-validated');
        
        // Clear file previews
        uploadedFiles = [];
        document.getElementById('filePreviewContainer').innerHTML = '';
        
        if (suratId) {
            document.getElementById('suratModalTitle').textContent = 'Edit Surat Peringatan';
            document.getElementById('surat_id').value = suratId;
            this.loadSuratData(suratId);
        } else {
            document.getElementById('suratModalTitle').textContent = 'Buat Surat Peringatan';
            document.getElementById('surat_id').value = '';
            this.generateNomorSurat();
            setDefaultDates();
            document.getElementById('penandatangan').value = currentUserName;
        }
        
        this.suratModal.show();
    }
    
    showTemplateModal() {
        this.templateModal.show();
    }
    
    loadSuratData(id) {
        showLoading(true);
        fetch(`api/get_surat.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const surat = data.data;
                    document.getElementById('nomor_surat').value = surat.nomor_surat;
                    document.getElementById('mahasiswa_id').value = surat.mahasiswa_id;
                    document.getElementById('jenis_pelanggaran').value = surat.jenis_pelanggaran;
                    document.getElementById('keterangan').value = surat.keterangan;
                    document.getElementById('sanksi').value = surat.sanksi;
                    document.getElementById('tanggal_pelanggaran').value = surat.tanggal_pelanggaran.split(' ')[0];
                    document.getElementById('tanggal_surat').value = surat.tanggal_surat.split(' ')[0];
                    document.getElementById('penandatangan').value = surat.penandatangan;
                    document.getElementById('status').value = surat.status;
                    
                    // Load uploaded files if any
                    if (data.files && data.files.length > 0) {
                        this.displayExistingFiles(data.files);
                    }
                } else {
                    showAlert('Error', data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('Error', 'Gagal memuat data surat', 'error');
            })
            .finally(() => showLoading(false));
    }
    
    displayExistingFiles(files) {
        const previewContainer = document.getElementById('filePreviewContainer');
        files.forEach(file => {
            const fileId = file.id;
            const previewItem = document.createElement('div');
            previewItem.className = 'file-preview-item';
            previewItem.id = `file-${fileId}`;
            
            const icon = this.getFileIcon(file.type);
            const fileSize = formatFileSize(file.size);
            
            previewItem.innerHTML = `
                <div class="file-icon text-primary">
                    <i class="bi ${icon}"></i>
                </div>
                <div class="file-name text-ellipsis">
                    <a href="uploads/${file.file_path}" target="_blank">${file.original_name}</a>
                </div>
                <div class="file-size">${fileSize}</div>
                <div class="remove-file" onclick="suratModule.removeExistingFile(${fileId})">
                    <i class="bi bi-x-circle"></i>
                </div>
                <input type="hidden" name="existing_files[]" value="${fileId}">
            `;
            
            previewContainer.appendChild(previewItem);
        });
    }
    
    removeExistingFile(fileId) {
        const fileElement = document.getElementById(`file-${fileId}`);
        if (fileElement) {
            // Add to delete list
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_files[]';
            deleteInput.value = fileId;
            document.getElementById('formSuratPeringatan').appendChild(deleteInput);
            fileElement.remove();
        }
    }
    
    handleSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const isEdit = document.getElementById('surat_id').value !== '';
        
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        // Show loading on button
        const submitBtn = document.getElementById('suratSubmitBtn');
        const spinner = document.getElementById('suratSpinner');
        const btnText = document.getElementById('suratBtnText');
        
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Menyimpan...';
        
        // Prepare form data with files
        const formData = new FormData(form);
        
        // Add uploaded files
        uploadedFiles.forEach(file => {
            formData.append('files[]', file);
        });
        
        const url = isEdit ? 'api/update_surat.php' : 'api/tambah_surat.php';
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Berhasil!', data.message, 'success');
                this.suratModal.hide();
                refreshDashboard();
                if (document.getElementById('dynamicContent').style.display === 'block') {
                    const activeNav = document.querySelector('.nav-link.active');
                    if (activeNav && activeNav.textContent.includes('Arsip')) {
                        loadArsipContent();
                    }
                }
            } else {
                showAlert('Error', data.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Error', 'Terjadi kesalahan: ' + error, 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = 'Simpan Surat';
        });
    }
    
    applyTemplate(template) {
        let keterangan = '';
        let sanksi = '';
        
        switch(template) {
            case 'akademik':
                keterangan = 'Mahasiswa tidak mengumpulkan tugas mata kuliah minimal 3 kali berturut-turut atau memiliki nilai di bawah standar yang ditetapkan.';
                sanksi = 'Peringatan tertulis pertama, wajib konsultasi dengan dosen pengampu, dan mengumpulkan semua tugas yang tertunggak dalam waktu 7 hari kerja.';
                document.getElementById('jenis_pelanggaran').value = 'akademik';
                break;
            case 'etika':
                keterangan = 'Mahasiswa melakukan pelanggaran etika seperti terlambat masuk kelas tanpa izin, tidak menghormati dosen atau teman sekelas, atau melakukan tindakan tidak terpuji lainnya.';
                sanksi = 'Peringatan tertulis, wajib membuat surat pernyataan, dan mengikuti sesi konseling dengan bagian kemahasiswaan.';
                document.getElementById('jenis_pelanggaran').value = 'etika';
                break;
            case 'administrasi':
                keterangan = 'Mahasiswa tidak memenuhi kewajiban administratif seperti keterlambatan pembayaran SPP, tidak melengkapi dokumen administrasi, atau tidak mengikuti prosedur yang ditetapkan.';
                sanksi = 'Peringatan tertulis, dikenakan denda sesuai ketentuan, dan wajib melengkapi administrasi dalam waktu 3 hari kerja.';
                document.getElementById('jenis_pelanggaran').value = 'administrasi';
                break;
            case 'umum':
                keterangan = 'Mahasiswa melanggar peraturan yang telah ditetapkan oleh institusi.';
                sanksi = 'Peringatan tertulis dan wajib mengikuti pembinaan.';
                document.getElementById('jenis_pelanggaran').value = 'lainnya';
                break;
        }
        
        document.getElementById('keterangan').value = keterangan;
        document.getElementById('sanksi').value = sanksi;
        
        this.templateModal.hide();
        showAlert('Template diterapkan!', 'Template berhasil diisi ke form.', 'success');
    }
    
    generateNomorSurat() {
        const currentYear = new Date().getFullYear();
        fetch(`api/get_nomor_surat.php?year=${currentYear}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('nomor_surat').value = data.nomor_surat;
                } else {
                    // Fallback
                    const randomNum = Math.floor(Math.random() * 100) + 1;
                    document.getElementById('nomor_surat').value = `SP/${currentYear}/${randomNum.toString().padStart(3, '0')}`;
                }
            })
            .catch(() => {
                // Fallback
                const currentYear = new Date().getFullYear();
                const randomNum = Math.floor(Math.random() * 100) + 1;
                document.getElementById('nomor_surat').value = `SP/${currentYear}/${randomNum.toString().padStart(3, '0')}`;
            });
    }
    
    previewSurat(id) {
        showLoading(true);
        currentSuratId = id;
        
        fetch(`api/preview_surat.php?id=${id}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('previewContent').innerHTML = html;
                this.previewModal.show();
            })
            .catch(error => {
                showAlert('Error', 'Gagal memuat preview surat', 'error');
            })
            .finally(() => showLoading(false));
    }
    
    downloadSuratPDF(id) {
        window.open(`api/generate_pdf.php?id=${id}`, '_blank');
    }
    
    hapusSurat(id) {
        Swal.fire({
            title: 'Hapus Surat?',
            text: 'Data surat akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading(true);
                fetch('api/hapus_surat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Berhasil!', data.message, 'success');
                        refreshDashboard();
                        if (document.getElementById('dynamicContent').style.display === 'block') {
                            loadArsipData();
                        }
                    } else {
                        showAlert('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    showAlert('Error', 'Gagal menghapus surat', 'error');
                })
                .finally(() => showLoading(false));
            }
        });
    }
    
    getFileIcon(fileType) {
        if (fileType.includes('jpeg') || fileType.includes('png') || fileType.includes('gif')) return 'bi-file-image';
        if (fileType.includes('pdf')) return 'bi-file-pdf';
        if (fileType.includes('word')) return 'bi-file-word';
        return 'bi-file-earmark';
    }
}

// Create instance
const suratModule = new SuratModule();