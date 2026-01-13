// ========== FUNGSI INISIALISASI BARU ==========
function initializeAllEventListeners() {
    console.log('🔧 Initializing all event listeners...');
    
    // Menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', toggleSidebar);
    }
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', toggleSidebar);
    }
    
    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
    
    // Logout
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', showLogoutModal);
    }
    
    // Navigation
    setupNavigationListeners();
    
    // Quick actions
    setupQuickActionListeners();
    
    // Back buttons
    setupBackButtonListeners();
    
    // Search and filter
    setupSearchFilterListeners();
    
    // Pengaturan
    setupPengaturanListeners();
    
    // PERBAIKAN 1: Event listener untuk form mahasiswa - MENGGANTI ID
    const simpanMahasiswaBtn = document.getElementById('simpanMahasiswa') || 
                               document.getElementById('btnSimpanMahasiswa');
    if (simpanMahasiswaBtn) {
        console.log('✅ Found simpan mahasiswa button');
        simpanMahasiswaBtn.addEventListener('click', simpanDataMahasiswa);
    } else {
        console.error('❌ Simpan mahasiswa button not found!');
    }
    
    // PERBAIKAN 2: Tombol tambah mahasiswa - MENGGANTI ID
    const tambahMahasiswaBtn = document.getElementById('tambahMahasiswa') || 
                               document.getElementById('btnTambahMahasiswa');
    if (tambahMahasiswaBtn) {
        console.log('✅ Found tambah mahasiswa button');
        tambahMahasiswaBtn.addEventListener('click', () => {
            console.log('🔼 Tambah mahasiswa button clicked');
            showTambahMahasiswaModal();
        });
    } else {
        console.error('❌ Tambah mahasiswa button not found!');
        // Coba temukan tombol lain
        const buttons = document.querySelectorAll('button');
        buttons.forEach(btn => {
            if (btn.textContent.includes('Mahasiswa') || btn.textContent.includes('Tambah')) {
                console.log('Found potential button:', btn.id, btn.textContent);
            }
        });
    }
    
    // Tambahkan event listener untuk form surat
    const simpanSuratBtn = document.getElementById('simpanSurat');
    if (simpanSuratBtn) {
        simpanSuratBtn.addEventListener('click', simpanSuratPeringatan);
    }
    
    // Mahasiswa dropdown change
    const mahasiswaSelect = document.getElementById('mahasiswa');
    if (mahasiswaSelect) {
        mahasiswaSelect.addEventListener('change', updateMahasiswaInfo);
    }
    
    // Tambah surat dari riwayat
    const tambahSuratRiwayatBtn = document.getElementById('btnTambahSuratRiwayat');
    if (tambahSuratRiwayatBtn) {
        tambahSuratRiwayatBtn.addEventListener('click', () => showSuratModal());
    }
    
    // Refresh button
    const refreshBtn = document.getElementById('btnRefreshSurat');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshSuratTerbaru);
    }
    
    console.log('✅ All event listeners initialized');
}

function setupNavigationListeners() {
    const navIds = ['navDashboard', 'navRiwayat', 'navArsipLink', 'navDataMahasiswaLink', 'navPengaturan'];
    
    navIds.forEach(navId => {
        const element = document.getElementById(navId);
        if (element) {
            element.addEventListener('click', (e) => {
                e.preventDefault();
                switch(navId) {
                    case 'navDashboard': showDashboardMain(); break;
                    case 'navRiwayat': showRiwayatPage(); break;
                    case 'navArsipLink': showArsipPage(); break;
                    case 'navDataMahasiswaLink': showDataMahasiswaPage(); break;
                    case 'navPengaturan': showPengaturanPage(); break;
                }
            });
        }
    });
}

function setupQuickActionListeners() {
    const actions = {
        'btnBuatSurat': () => showSuratModal(),
        'btnTemplateSurat': () => showTemplateModal(),
        'btnLihatSurat': () => showRiwayatPage(),
        'btnRiwayat': () => showRiwayatPage(),
        'btnExportPDF': () => exportDashboardPDF()
    };
    
    Object.keys(actions).forEach(btnId => {
        const element = document.getElementById(btnId);
        if (element) {
            element.addEventListener('click', actions[btnId]);
        }
    });
}

function setupBackButtonListeners() {
    const backButtons = {
        'kembaliDariRiwayat': showDashboardMain,
        'kembaliDariArsip': showDashboardMain,
        'kembaliDariMahasiswa': showDashboardMain,
        'kembaliDariPengaturan': showDashboardMain
    };
    
    Object.keys(backButtons).forEach(btnId => {
        const element = document.getElementById(btnId);
        if (element) {
            element.addEventListener('click', backButtons[btnId]);
        }
    });
}

function setupSearchFilterListeners() {
    const searchActions = {
        'btnCariRiwayat': filterRiwayat,
        'btnResetRiwayat': resetFilterRiwayat,
        'btnCariArsip': filterArsip,
        'btnResetArsip': resetFilterArsip,
        'btnCariMahasiswa': filterMahasiswa,
        'btnResetMahasiswa': resetFilterMahasiswa
    };
    
    Object.keys(searchActions).forEach(btnId => {
        const element = document.getElementById(btnId);
        if (element) {
            element.addEventListener('click', searchActions[btnId]);
        }
    });
}

function setupPengaturanListeners() {
    const pengaturanActions = {
        'simpanPengaturanUmum': simpanPengaturanUmum,
        'btnSimpanAkun': simpanAkun,
        'btnResetFormAkun': resetFormAkun,
        'btnBackupSekarang': backupSekarang,
        'btnResetSistem': resetSistem,
        'togglePasswordAkun': togglePasswordAkun
    };
    
    Object.keys(pengaturanActions).forEach(btnId => {
        const element = document.getElementById(btnId);
        if (element) {
            element.addEventListener('click', pengaturanActions[btnId]);
        }
    });
    
    // File restore
    const restoreFile = document.getElementById('restoreFile');
    if (restoreFile) {
        restoreFile.addEventListener('change', handleRestoreFile);
    }
    
    const btnRestoreBackup = document.getElementById('btnRestoreBackup');
    if (btnRestoreBackup) {
        btnRestoreBackup.addEventListener('click', () => {
            document.getElementById('restoreFile').click();
        });
    }
}

// ========== FILE UPLOAD FUNCTIONS ==========
function setupFileUploadListeners() {
    console.log('📎 Setting up file upload listeners...');
    
    // Upload JPG
    const uploadJPGBtn = document.getElementById('uploadJPG');
    const fileJPGInput = document.getElementById('fileJPG');
    
    if (uploadJPGBtn && fileJPGInput) {
        uploadJPGBtn.addEventListener('click', () => {
            fileJPGInput.click();
        });
        
        fileJPGInput.addEventListener('change', (e) => {
            handleFileUpload(e, 'image');
        });
    }
    
    // Upload Document
    const uploadDocumentBtn = document.getElementById('uploadDocument');
    const fileDocumentInput = document.getElementById('fileDocument');
    
    if (uploadDocumentBtn && fileDocumentInput) {
        uploadDocumentBtn.addEventListener('click', () => {
            fileDocumentInput.click();
        });
        
        fileDocumentInput.addEventListener('change', (e) => {
            handleFileUpload(e, 'document');
        });
    }
}

function handleFileUpload(event, type) {
    const file = event.target.files[0];
    if (!file) return;
    
    console.log('📁 File selected:', file.name, 'Type:', type, 'Size:', file.size);
    
    // Check file size (max 5MB)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        showNotification(`File ${file.name} terlalu besar! Maksimal 5MB`, 'error');
        event.target.value = ''; // Reset input
        return;
    }
    
    // Validate file type
    let isValidType = false;
    if (type === 'image') {
        const validImageTypes = ['image/jpeg', 'image/jpg'];
        isValidType = validImageTypes.includes(file.type.toLowerCase());
        if (!isValidType) {
            showNotification('Format file gambar harus JPG/JPEG', 'error');
            event.target.value = '';
            return;
        }
    } else if (type === 'document') {
        const validExtensions = ['.pdf', '.doc', '.docx', '.xls', '.xlsx'];
        const fileName = file.name.toLowerCase();
        isValidType = validExtensions.some(ext => fileName.endsWith(ext));
        
        if (!isValidType) {
            showNotification('Format file harus PDF, DOC, DOCX, XLS, atau XLSX', 'error');
            event.target.value = '';
            return;
        }
    }
    
    // Add to uploaded files
    const fileData = {
        name: file.name,
        size: file.size,
        type: type,
        fileObject: file,
        uploadedAt: new Date().toISOString()
    };
    
    if (type === 'image') {
        uploadedFiles.images = [fileData]; // Hanya satu gambar
        const jpgInfo = document.getElementById('jpgInfo');
        if (jpgInfo) {
            jpgInfo.innerHTML = `
                <i class="fas fa-check-circle" style="color: var(--success); margin-right: 5px;"></i>
                ${file.name} (${formatFileSize(file.size)})
            `;
        }
    } else if (type === 'document') {
        uploadedFiles.documents = [fileData]; // Hanya satu dokumen
        const documentInfo = document.getElementById('documentInfo');
        if (documentInfo) {
            documentInfo.innerHTML = `
                <i class="fas fa-check-circle" style="color: var(--success); margin-right: 5px;"></i>
                ${file.name} (${formatFileSize(file.size)})
            `;
        }
    }
    
    showNotification(`File ${file.name} berhasil diupload`, 'success');
    updateFilePreview();
}

function updateFilePreview() {
    const container = document.getElementById('filePreviewContainer');
    const list = document.getElementById('previewList');
    
    if (!container || !list) return;
    
    const allFiles = [...uploadedFiles.images, ...uploadedFiles.documents];
    
    if (allFiles.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    list.innerHTML = '';
    
    allFiles.forEach((file, index) => {
        const fileType = file.type === 'image' ? 'Gambar' : 'Dokumen';
        const fileIcon = file.type === 'image' ? 'fa-image' : 'fa-file-alt';
        
        const item = document.createElement('div');
        item.className = 'preview-item';
        item.innerHTML = `
            <div class="preview-info">
                <div class="preview-icon">
                    <i class="fas ${fileIcon}"></i>
                </div>
                <div class="preview-details">
                    <h6>${file.name}</h6>
                    <p>${fileType} • ${formatFileSize(file.size)}</p>
                </div>
            </div>
            <div class="preview-actions">
                <button class="preview-action-btn" onclick="removeFile(${index}, '${file.type}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        list.appendChild(item);
    });
}

window.removeFile = function(index, type) {
    if (type === 'image') {
        uploadedFiles.images.splice(index, 1);
        const jpgInfo = document.getElementById('jpgInfo');
        if (jpgInfo) jpgInfo.innerHTML = '';
        const fileJPG = document.getElementById('fileJPG');
        if (fileJPG) fileJPG.value = '';
    } else if (type === 'document') {
        uploadedFiles.documents.splice(index, 1);
        const documentInfo = document.getElementById('documentInfo');
        if (documentInfo) documentInfo.innerHTML = '';
        const fileDocument = document.getElementById('fileDocument');
        if (fileDocument) fileDocument.value = '';
    }
    
    showNotification('File berhasil dihapus', 'success');
    updateFilePreview();
};

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}