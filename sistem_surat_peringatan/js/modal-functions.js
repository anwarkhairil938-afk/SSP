// ========== MODAL FUNCTIONS ==========
function setupModalEventListeners() {
    // Logout modal
    setupModalCloseListeners('logoutModal', ['closeLogoutModal', 'cancelLogout']);
    document.getElementById('confirmLogout')?.addEventListener('click', handleLogout);
    
    // Surat modal
    setupModalCloseListeners('suratPeringatanModal', ['closeSuratModal', 'cancelSurat']);
    document.getElementById('btnPreviewSurat')?.addEventListener('click', previewSurat);
    
    // Template modal
    setupModalCloseListeners('templateSuratModal', ['closeTemplateModal', 'closeTemplateModalBtn']);
    
    // Mahasiswa modal
    setupModalCloseListeners('tambahMahasiswaModal', ['closeTambahMahasiswaModal', 'cancelTambahMahasiswa']);
    
    // Tampilan surat modal - FITUR BARU: tambah event listener untuk download PDF
    setupModalCloseListeners('tampilanSuratModal', ['closeTampilanSuratModal', 'tutupSurat']);
    document.getElementById('btnExportPDFModal')?.addEventListener('click', exportToPDF);
    
    // PERBAIKAN 3: Tambah tombol download surat langsung setelah preview
    const btnDownloadSurat = document.getElementById('btnDownloadSurat');
    if (!btnDownloadSurat) {
        // Tambahkan tombol jika belum ada
        const modalContent = document.querySelector('#tampilanSuratModal .modal-content');
        if (modalContent) {
            const modalHeader = modalContent.querySelector('.modal-header');
            if (modalHeader) {
                const downloadBtn = document.createElement('button');
                downloadBtn.id = 'btnDownloadSurat';
                downloadBtn.className = 'btn btn-success';
                downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download PDF';
                downloadBtn.addEventListener('click', downloadSuratPDF);
                modalHeader.appendChild(downloadBtn);
            }
        }
    } else {
        btnDownloadSurat.addEventListener('click', downloadSuratPDF);
    }
    
    // Confirm modal
    setupModalCloseListeners('confirmModal', ['closeConfirmModal', 'cancelConfirm']);
    
    // Template selection
    document.querySelectorAll('.template-card').forEach(card => {
        card.addEventListener('click', function() {
            const template = this.getAttribute('data-template');
            applyTemplate(template);
            hideModal('templateSuratModal');
        });
    });
}

function setupModalCloseListeners(modalId, closeButtonIds) {
    closeButtonIds.forEach(buttonId => {
        const button = document.getElementById(buttonId);
        if (button) {
            button.addEventListener('click', () => hideModal(modalId));
        }
    });
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.opacity = '1';
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    modalContent.style.transform = 'scale(1)';
                }, 10);
            }
        }, 10);
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.opacity = '0';
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.transform = 'scale(0.9)';
        }
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

// ========== MAHASISWA FUNCTIONS ==========
function showTambahMahasiswaModal(mahasiswaId = null) {
    console.log('📝 Opening mahasiswa modal for:', mahasiswaId ? 'edit' : 'add');
    
    // Reset form
    const form = document.getElementById('formTambahMahasiswa');
    if (form) {
        form.reset();
    }
    
    const modalTitle = document.getElementById('tambahMahasiswaModalTitle');
    
    // PERBAIKAN 4: Reset editingMahasiswaId dengan benar
    if (mahasiswaId) {
        if (modalTitle) modalTitle.textContent = 'Edit Mahasiswa';
        editingMahasiswaId = mahasiswaId;
        
        // Load data mahasiswa
        const mahasiswa = db.getMahasiswaById(mahasiswaId);
        console.log('Loading mahasiswa data:', mahasiswa);
        
        if (mahasiswa) {
            document.getElementById('namaMahasiswa').value = mahasiswa.nama || '';
            document.getElementById('nimMahasiswa').value = mahasiswa.nim || '';
            document.getElementById('prodiMahasiswa').value = mahasiswa.programStudi || '';
            document.getElementById('semesterMahasiswa').value = mahasiswa.semester || '';
            document.getElementById('usernameMahasiswa').value = mahasiswa.username || '';
            document.getElementById('passwordMahasiswa').value = mahasiswa.password || '';
            document.getElementById('roleMahasiswa').value = mahasiswa.peran || 'mahasiswa';
            document.getElementById('statusMahasiswa').value = mahasiswa.status || 'aktif';
        }
    } else {
        if (modalTitle) modalTitle.textContent = 'Tambah Mahasiswa';
        editingMahasiswaId = null; // PASTIKAN di-reset ke null
        
        // Set default values
        document.getElementById('roleMahasiswa').value = 'mahasiswa';
        document.getElementById('statusMahasiswa').value = 'aktif';
        document.getElementById('semesterMahasiswa').value = '1';
        
        // PERBAIKAN 5: Generate username dan password default
        const usernameInput = document.getElementById('usernameMahasiswa');
        const passwordInput = document.getElementById('passwordMahasiswa');
        if (usernameInput && !usernameInput.value) {
            usernameInput.value = '';
        }
        if (passwordInput && !passwordInput.value) {
            passwordInput.value = 'mahasiswa123';
        }
    }
    
    showModal('tambahMahasiswaModal');
}

function simpanDataMahasiswa() {
    console.log('💾 Saving mahasiswa data...');
    console.log('Editing ID:', editingMahasiswaId); // Debug
    
    // Validasi form
    const form = document.getElementById('formTambahMahasiswa');
    if (!form.checkValidity()) {
        form.reportValidity();
        showNotification('Harap lengkapi semua field yang wajib diisi!', 'error');
        return;
    }
    
    const nama = document.getElementById('namaMahasiswa').value.trim();
    const nim = document.getElementById('nimMahasiswa').value.trim();
    const prodi = document.getElementById('prodiMahasiswa').value;
    const semester = document.getElementById('semesterMahasiswa').value;
    const username = document.getElementById('usernameMahasiswa').value.trim();
    const password = document.getElementById('passwordMahasiswa').value;
    const peran = document.getElementById('roleMahasiswa').value;
    const status = document.getElementById('statusMahasiswa').value;
    
    console.log('📋 Data to save:', { nama, nim, prodi, semester, username, peran, status });
    
    const mahasiswaData = {
        nama: nama,
        nim: nim,
        programStudi: prodi,
        semester: semester,
        username: username,
        password: password,
        peran: peran,
        status: status
    };
    
    let result = null;
    let message = '';
    let success = false;
    
    if (editingMahasiswaId) {
        console.log('🔄 Updating existing mahasiswa ID:', editingMahasiswaId);
        result = db.updateMahasiswa(editingMahasiswaId, mahasiswaData);
        success = result.success;
        message = success ? 'Data mahasiswa berhasil diperbarui!' : result.message || 'Gagal memperbarui data mahasiswa!';
    } else {
        console.log('➕ Adding new mahasiswa');
        result = db.addMahasiswa(mahasiswaData);
        success = result.success;
        message = success ? 'Mahasiswa berhasil ditambahkan!' : result.message || 'Gagal menambahkan mahasiswa!';
    }
    
    if (success) {
        showNotification(message, 'success');
        
        // Refresh semua UI yang terkait
        setTimeout(() => {
            isiMahasiswa();
            isiDropdownMahasiswa();
            updateStats();
            isiAkunTable();
            
            // Reset form dan tutup modal
            form.reset();
            hideModal('tambahMahasiswaModal');
            editingMahasiswaId = null; // PASTIKAN di-reset
            
            console.log('✅ Mahasiswa saved successfully');
            console.log('📊 Current mahasiswa count:', db.getAllMahasiswa().length);
        }, 300);
    } else {
        showNotification(message, 'error');
        console.error('❌ Failed to save mahasiswa:', result);
    }
}

// ========== SURAT PERINGATAN FUNCTIONS ==========
function showSuratModal(suratId = null) {
    console.log('📝 Opening surat modal for:', suratId ? 'edit' : 'add');
    
    // Reset form
    const form = document.getElementById('formSuratPeringatan');
    if (form) {
        form.reset();
        // Reset file upload
        uploadedFiles = { images: [], documents: [] };
        updateFilePreview();
        document.getElementById('jpgInfo').innerHTML = '';
        document.getElementById('documentInfo').innerHTML = '';
    }
    
    // Set tanggal default
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggalSurat').value = today;
    document.getElementById('tanggalPelanggaran').value = today;
    
    const modalTitle = document.getElementById('modalSuratTitle');
    
    if (suratId) {
        if (modalTitle) modalTitle.textContent = 'Edit Surat Peringatan';
        editingSuratId = suratId;
        
        // Load data surat
        const surat = db.getSuratById(suratId);
        console.log('Loading surat data:', surat);
        
        if (surat) {
            document.getElementById('nomorSurat').value = surat.nomorSurat || '';
            document.getElementById('mahasiswa').value = surat.mahasiswaId || '';
            document.getElementById('jenisPelanggaran').value = surat.jenisPelanggaran || '';
            document.getElementById('keterangan').value = surat.keterangan || '';
            document.getElementById('sanksi').value = surat.sanksi || '';
            document.getElementById('tanggalSurat').value = surat.tanggalSurat || '';
            document.getElementById('tanggalPelanggaran').value = surat.tanggalPelanggaran || '';
            document.getElementById('penandatangan').value = surat.penandatangan || '';
            document.getElementById('statusSurat').value = surat.status || 'pending';
            
            // Update mahasiswa info
            updateMahasiswaInfo();
        }
    } else {
        if (modalTitle) modalTitle.textContent = 'Buat Surat Peringatan';
        editingSuratId = null;
        
        // Generate nomor surat
        const currentYear = new Date().getFullYear();
        const totalSurat = db.getAllSurat().length + 1;
        document.getElementById('nomorSurat').value = `SP/${currentYear}/${totalSurat.toString().padStart(3, '0')}`;
    }
    
    showModal('suratPeringatanModal');
}

function updateMahasiswaInfo() {
    const mahasiswaId = document.getElementById('mahasiswa').value;
    if (!mahasiswaId) return;
    
    const mahasiswa = db.getMahasiswaById(parseInt(mahasiswaId));
    if (mahasiswa) {
        document.getElementById('programStudi').value = mahasiswa.programStudi || '';
        document.getElementById('semester').value = mahasiswa.semester || '';
    }
}

function previewSurat() {
    // Ambil data dari form
    const nomorSurat = document.getElementById('nomorSurat').value;
    const mahasiswaId = document.getElementById('mahasiswa').value;
    const jenisPelanggaran = document.getElementById('jenisPelanggaran').value;
    const keterangan = document.getElementById('keterangan').value;
    const sanksi = document.getElementById('sanksi').value;
    const tanggalSurat = document.getElementById('tanggalSurat').value;
    const tanggalPelanggaran = document.getElementById('tanggalPelanggaran').value;
    const penandatangan = document.getElementById('penandatangan').value;
    const status = document.getElementById('statusSurat').value;
    
    // Validasi
    if (!nomorSurat || !mahasiswaId || !jenisPelanggaran || !keterangan || !sanksi || !tanggalSurat || !tanggalPelanggaran || !penandatangan) {
        showNotification('Harap lengkapi semua field yang wajib diisi!', 'error');
        return;
    }
    
    const mahasiswa = db.getMahasiswaById(parseInt(mahasiswaId));
    if (!mahasiswa) {
        showNotification('Mahasiswa tidak ditemukan!', 'error');
        return;
    }
    
    // Simpan surat sementara untuk preview
    currentSuratView = {
        nomorSurat: nomorSurat,
        mahasiswaId: parseInt(mahasiswaId),
        mahasiswa: mahasiswa.nama,
        nim: mahasiswa.nim,
        jenisPelanggaran: jenisPelanggaran,
        keterangan: keterangan,
        sanksi: sanksi,
        tanggalSurat: tanggalSurat,
        tanggalPelanggaran: tanggalPelanggaran,
        penandatangan: penandatangan,
        status: status,
        lampiran: [...uploadedFiles.images, ...uploadedFiles.documents],
        programStudi: mahasiswa.programStudi,
        semester: mahasiswa.semester
    };
    
    // Tampilkan preview
    const suratContent = document.getElementById('suratContent');
    if (suratContent) {
        suratContent.innerHTML = buatTemplateSurat(currentSuratView, mahasiswa);
    }
    
    hideModal('suratPeringatanModal');
    showModal('tampilanSuratModal');
}

function simpanSuratPeringatan() {
    console.log('💾 Saving surat data...');
    
    const nomorSurat = document.getElementById('nomorSurat').value;
    const mahasiswaId = document.getElementById('mahasiswa').value;
    const jenisPelanggaran = document.getElementById('jenisPelanggaran').value;
    const keterangan = document.getElementById('keterangan').value;
    const sanksi = document.getElementById('sanksi').value;
    const tanggalSurat = document.getElementById('tanggalSurat').value;
    const tanggalPelanggaran = document.getElementById('tanggalPelanggaran').value;
    const penandatangan = document.getElementById('penandatangan').value;
    const status = document.getElementById('statusSurat').value;
    
    console.log('📋 Data to save:', {
        nomorSurat, mahasiswaId, jenisPelanggaran, keterangan, sanksi,
        tanggalSurat, tanggalPelanggaran, penandatangan, status
    });
    
    // Validasi
    if (!nomorSurat || !mahasiswaId || !jenisPelanggaran || !keterangan || !sanksi || !tanggalSurat || !tanggalPelanggaran || !penandatangan) {
        showNotification('Harap lengkapi semua field yang wajib diisi!', 'error');
        return;
    }
    
    const mahasiswa = db.getMahasiswaById(parseInt(mahasiswaId));
    if (!mahasiswa) {
        showNotification('Mahasiswa tidak ditemukan!', 'error');
        return;
    }
    
    const suratData = {
        nomorSurat: nomorSurat,
        mahasiswaId: parseInt(mahasiswaId),
        mahasiswa: mahasiswa.nama,
        nim: mahasiswa.nim,
        jenisPelanggaran: jenisPelanggaran,
        keterangan: keterangan,
        sanksi: sanksi,
        tanggalSurat: tanggalSurat,
        tanggalPelanggaran: tanggalPelanggaran,
        penandatangan: penandatangan,
        status: status,
        lampiran: [...uploadedFiles.images, ...uploadedFiles.documents]
    };
    
    let result = null;
    let message = '';
    
    if (editingSuratId) {
        // Edit surat yang sudah ada
        console.log('🔄 Updating existing surat ID:', editingSuratId);
        result = db.updateSurat(editingSuratId, suratData);
        message = result ? 'Surat berhasil diperbarui!' : 'Gagal memperbarui surat!';
    } else {
        // Tambah surat baru
        console.log('➕ Adding new surat');
        result = db.addSurat(suratData);
        message = result ? 'Surat berhasil ditambahkan!' : 'Gagal menambahkan surat!';
    }
    
    if (result) {
        showNotification(message, 'success');
        
        // Update UI
        isiSuratTerbaru();
        isiRiwayat();
        isiArsip();
        updateStats();
        
        // Reset dan tutup modal
        uploadedFiles = { images: [], documents: [] };
        hideModal('suratPeringatanModal');
        
        // Tampilkan opsi untuk download PDF
        setTimeout(() => {
            if (confirm('Surat berhasil disimpan! Apakah Anda ingin mendownload PDF surat ini?')) {
                // Simpan surat yang baru dibuat untuk download
                if (!editingSuratId && result) {
                    currentSuratView = {
                        ...suratData,
                        programStudi: mahasiswa.programStudi,
                        semester: mahasiswa.semester
                    };
                    downloadSuratPDF();
                }
            }
        }, 500);
    } else {
        showNotification(message, 'error');
    }
}

function buatTemplateSurat(surat, mahasiswa) {
    const jenisText = {
        'akademik': 'Pelanggaran Akademik',
        'etika': 'Pelanggaran Etika',
        'administrasi': 'Pelanggaran Administrasi',
        'lainnya': 'Pelanggaran Lainnya'
    }[surat.jenisPelanggaran] || 'Pelanggaran';
    
    const pengaturan = db.getPengaturan();
    
    return `
        <div class="surat-container" id="suratUntukPDF">
            <div class="surat-header">
                <h1>SURAT PERINGATAN</h1>
                <h2>${pengaturan.namaInstitusi}</h2>
                <p>${pengaturan.alamatInstitusi}</p>
                <p>Telp: ${pengaturan.telpInstitusi} | Email: ${pengaturan.emailInstitusi}</p>
            </div>
            
            <div class="surat-content">
                <div class="surat-field">
                    <label>Nomor:</label>
                    <span>${surat.nomorSurat}</span>
                </div>
                <div class="surat-field">
                    <label>Tanggal:</label>
                    <span>${formatTanggalIndonesia(surat.tanggalSurat)}</span>
                </div>
                
                <div class="surat-paragraph">
                    <p>Yang bertanda tangan di bawah ini:</p>
                    <p><strong>${surat.penandatangan}</strong></p>
                    <p>Dengan ini memberikan Surat Peringatan kepada:</p>
                </div>
                
                <div class="surat-field">
                    <label>Nama:</label>
                    <span>${surat.mahasiswa}</span>
                </div>
                <div class="surat-field">
                    <label>NIM:</label>
                    <span>${surat.nim}</span>
                </div>
                <div class="surat-field">
                    <label>Program Studi:</label>
                    <span>${mahasiswa?.programStudi || surat.programStudi || '-'}</span>
                </div>
                <div class="surat-field">
                    <label>Semester:</label>
                    <span>${mahasiswa?.semester || surat.semester || '-'}</span>
                </div>
                
                <div class="surat-paragraph">
                    <p><strong>Jenis Pelanggaran:</strong> ${jenisText}</p>
                    <p><strong>Keterangan:</strong></p>
                    <p>${surat.keterangan.replace(/\n/g, '<br>')}</p>
                    <p><strong>Sanksi:</strong></p>
                    <p>${surat.sanksi.replace(/\n/g, '<br>')}</p>
                </div>
                
                <div class="surat-paragraph">
                    <p>Surat peringatan ini diberikan atas pelanggaran yang terjadi pada tanggal: <strong>${formatTanggalIndonesia(surat.tanggalPelanggaran)}</strong></p>
                </div>
            </div>
            
            <div class="surat-footer">
                <div class="ttd-container">
                    <div class="ttd-space"></div>
                    <p><strong>${surat.penandatangan}</strong></p>
                </div>
            </div>
            
            ${surat.lampiran && surat.lampiran.length > 0 ? `
            <div class="lampiran-section">
                <h4>Lampiran:</h4>
                <ul>
                    ${surat.lampiran.map(file => 
                        `<li>${file.name} (${formatFileSize(file.size)})</li>`
                    ).join('')}
                </ul>
            </div>
            ` : ''}
        </div>
    `;
}