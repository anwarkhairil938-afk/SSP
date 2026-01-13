// ========== INISIALISASI ==========
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing dashboard...');
    
    // Prevent JS conflicts
    if (window.jQuery) {
        jQuery.noConflict();
    }
    
    initializeDashboard();
    initializeAllEventListeners();
    updateClock();
    setInterval(updateClock, 1000);
    setupFileUploadListeners();
    setupPengaturanTabs();
    loadPengaturanData();
    
    // Setup modal event listeners
    setupModalEventListeners();
    
    // Setup PDF library jika belum ada
    setupPDFLibrary();
    
    // Panggil fungsi repair jika ada masalah
    setTimeout(() => {
        if (db.getAllMahasiswa().length === 0 || db.getAllSurat().length === 0) {
            console.warn('⚠️ System data appears empty, attempting repair...');
            repairSystem();
        }
    }, 2000);
});

// ========== DASHBOARD FUNCTIONS ==========
function initializeDashboard() {
    // Cek tema yang disimpan
    const savedTheme = localStorage.getItem('dashboardTheme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        isDarkTheme = true;
    }
    
    // Cek user dari session (simulasi)
    currentUser = {
        username: 'admin',
        peran: 'admin',
        name: 'Admin Sistem'
    };
    
    // Load data
    console.log('📊 Loading dashboard data...');
    loadDashboardData();
}

function loadDashboardData() {
    updateStats();
    isiSuratTerbaru();
    isiDropdownMahasiswa();
    isiRiwayat();
    isiArsip();
    isiMahasiswa();
    isiAkunTable();
    
    console.log('✅ Dashboard data loaded');
}

// ========== PENGATURAN FUNCTIONS ==========
function setupPengaturanTabs() {
    console.log('🔧 Setting up pengaturan tabs...');
    
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            const tabContent = document.getElementById(tabId);
            if (tabContent) {
                tabContent.classList.add('active');
            }
        });
    });
}

function loadPengaturanData() {
    const pengaturan = db.getPengaturan();
    
    // Update form values
    const formElements = {
        'namaAplikasi': pengaturan.namaAplikasi || '',
        'versiAplikasi': pengaturan.versiAplikasi || '',
        'namaInstitusi': pengaturan.namaInstitusi || '',
        'alamatInstitusi': pengaturan.alamatInstitusi || '',
        'telpInstitusi': pengaturan.telpInstitusi || '',
        'emailInstitusi': pengaturan.emailInstitusi || ''
    };
    
    Object.keys(formElements).forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            element.value = formElements[elementId];
        }
    });
    
    // Update backup info
    const lastBackupTime = document.getElementById('lastBackupTime');
    if (lastBackupTime) {
        lastBackupTime.textContent = pengaturan.backupTerakhir ? 
            new Date(pengaturan.backupTerakhir).toLocaleString('id-ID') : 'Belum pernah';
    }
    
    // Update system info
    const systemInfoElements = {
        'systemVersion': pengaturan.versiAplikasi || '1.0.0',
        'totalSuratSistem': db.getAllSurat().length,
        'totalMahasiswaSistem': db.getAllMahasiswa().filter(m => m.status === 'aktif').length,
        'browserInfo': navigator.userAgent
    };
    
    Object.keys(systemInfoElements).forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = systemInfoElements[elementId];
        }
    });
}

function simpanPengaturanUmum() {
    const data = {
        namaAplikasi: document.getElementById('namaAplikasi').value,
        versiAplikasi: document.getElementById('versiAplikasi').value,
        namaInstitusi: document.getElementById('namaInstitusi').value,
        alamatInstitusi: document.getElementById('alamatInstitusi').value,
        telpInstitusi: document.getElementById('telpInstitusi').value,
        emailInstitusi: document.getElementById('emailInstitusi').value
    };
    
    const success = db.updatePengaturan(data);
    if (success) {
        showNotification('Pengaturan berhasil disimpan!', 'success');
        loadPengaturanData();
    } else {
        showNotification('Gagal menyimpan pengaturan!', 'error');
    }
}

function simpanAkun() {
    const username = document.getElementById('usernameAkun').value.trim();
    const nama = document.getElementById('namaAkun').value.trim();
    const password = document.getElementById('passwordAkun').value;
    const peran = document.getElementById('roleAkun').value;
    const status = document.getElementById('statusAkun').value;
    const nim = document.getElementById('nimAkun').value.trim();
    
    // Validasi
    if (!username || !nama || !password || !peran || !status) {
        showNotification('Harap lengkapi semua field yang wajib diisi!', 'error');
        return;
    }
    
    const akunData = {
        username: username,
        nama: nama,
        password: password,
        peran: peran,
        status: status,
        nim: nim
    };
    
    let success = false;
    let message = '';
    
    if (editingAkunId) {
        success = db.updateAkun(editingAkunId, akunData);
        message = success ? 'Akun berhasil diperbarui!' : 'Gagal memperbarui akun!';
        editingAkunId = null;
    } else {
        const result = db.addAkun(akunData);
        success = !!result;
        message = success ? 'Akun berhasil ditambahkan!' : 'Gagal menambahkan akun!';
    }
    
    if (success) {
        showNotification(message, 'success');
        resetFormAkun();
        isiAkunTable();
        isiMahasiswa();
        updateStats();
    } else {
        showNotification(message, 'error');
    }
}

function resetFormAkun() {
    const form = document.getElementById('formTambahAkun');
    if (form) {
        form.reset();
    }
    editingAkunId = null;
}

function togglePasswordAkun() {
    const passwordInput = document.getElementById('passwordAkun');
    const icon = document.getElementById('togglePasswordAkun')?.querySelector('i');
    
    if (passwordInput && icon) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
}

function backupSekarang() {
    const backupData = db.backupDatabase();
    const dataStr = JSON.stringify(backupData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportFileDefaultName = `backup-surat-peringatan-${new Date().toISOString().slice(0,10)}.json`;
    
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    
    showNotification('Backup berhasil diekspor!', 'success');
    loadPengaturanData();
}

function handleRestoreFile(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!confirm('PERINGATAN: Restore backup akan mengganti semua data yang ada. Pastikan Anda sudah membackup data terbaru. Lanjutkan?')) {
        event.target.value = '';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const backupData = JSON.parse(e.target.result);
            const success = db.restoreDatabase(backupData);
            if (success) {
                showNotification('Backup berhasil direstore!', 'success');
                loadDashboardData();
                loadPengaturanData();
            } else {
                showNotification('Gagal restore backup!', 'error');
            }
        } catch (error) {
            showNotification('File backup tidak valid!', 'error');
            console.error('Error parsing backup file:', error);
        }
        event.target.value = '';
    };
    reader.readAsText(file);
}

function resetSistem() {
    if (confirm('PERINGATAN: Reset sistem akan menghapus SEMUA data kecuali akun admin utama. Tindakan ini tidak dapat dibatalkan. Pastikan Anda sudah membackup data. Lanjutkan?')) {
        if (confirm('Apakah Anda BENAR-BENAR yakin? Semua data akan hilang!')) {
            const success = db.resetDatabase();
            if (success) {
                showNotification('Sistem berhasil direset!', 'success');
                loadDashboardData();
                loadPengaturanData();
            } else {
                showNotification('Gagal reset sistem!', 'error');
            }
        }
    }
}

// ========== NOTIFICATION FUNCTION ==========
function showNotification(message, type = 'info') {
    // Remove existing notification
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? 'var(--success)' : type === 'error' ? 'var(--danger)' : 'var(--primary)'};
        color: white;
        padding: 15px 20px;
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        max-width: 400px;
        z-index: 9999;
        box-shadow: var(--shadow);
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Add animation
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            .notification-content {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .notification-close {
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                font-size: 0.9rem;
            }
        `;
        document.head.appendChild(style);
    }
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
    
    // Close button
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    });
}

// ========== TEMPLATE FUNCTION ==========
function applyTemplate(templateType) {
    let keterangan = '';
    let sanksi = '';
    
    switch(templateType) {
        case 'template1': // Akademik
            keterangan = 'Mahasiswa tidak mengumpulkan tugas mata kuliah minimal 3 kali berturut-turut atau memiliki nilai di bawah standar yang ditetapkan.';
            sanksi = 'Peringatan tertulis pertama, wajib konsultasi dengan dosen pengampu, dan mengumpulkan semua tugas yang tertunggak dalam waktu 7 hari kerja.';
            break;
        case 'template2': // Etika
            keterangan = 'Mahasiswa melakukan pelanggaran etika seperti terlambat masuk kelas tanpa izin, tidak menghormati dosen atau teman sekelas, atau melakukan tindakan tidak terpuji lainnya.';
            sanksi = 'Peringatan tertulis, wajib membuat surat pernyataan, dan mengikuti sesi konseling dengan bagian kemahasiswaan.';
            break;
        case 'template3': // Administrasi
            keterangan = 'Mahasiswa tidak memenuhi kewajiban administratif seperti keterlambatan pembayaran SPP, tidak melengkapi dokumen administrasi, atau tidak mengikuti prosedur yang ditetapkan.';
            sanksi = 'Peringatan tertulis, dikenakan denda sesuai ketentuan, dan wajib melengkapi administrasi dalam waktu 3 hari kerja.';
            break;
    }
    
    // Isi ke form
    const keteranganInput = document.getElementById('keterangan');
    const sanksiInput = document.getElementById('sanksi');
    const jenisInput = document.getElementById('jenisPelanggaran');
    
    if (keteranganInput) keteranganInput.value = keterangan;
    if (sanksiInput) sanksiInput.value = sanksi;
    
    // Set jenis pelanggaran berdasarkan template
    if (jenisInput) {
        if (templateType === 'template1') {
            jenisInput.value = 'akademik';
        } else if (templateType === 'template2') {
            jenisInput.value = 'etika';
        } else if (templateType === 'template3') {
            jenisInput.value = 'administrasi';
        }
    }
    
    showNotification('Template berhasil diterapkan', 'success');
}

function showTemplateModal() {
    showModal('templateSuratModal');
}

function showLogoutModal() {
    showModal('logoutModal');
}

// ========== EXPORT FUNCTIONS ==========
function exportDashboardPDF() {
    showNotification('Fitur export dashboard PDF sedang dalam pengembangan', 'info');
}

function exportToPDF() {
    // Fungsi untuk export PDF dari modal
    if (currentSuratView) {
        downloadSuratPDF();
    } else {
        showNotification('Tidak ada surat untuk di-export!', 'error');
    }
}

// ========== LOGOUT FUNCTION ==========
function handleLogout() {
    showNotification('Berhasil keluar dari sistem', 'success');
    setTimeout(() => {
        // Redirect to login page
        window.location.href = 'login.php';
    }, 1500);
}

// ========== FUNGSI REPAIR SYSTEM ==========
function repairSystem() {
    console.log('🔧 Repairing system...');
    
    // Reset database
    db = new Database();
    
    // Clear all modals
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = 'none';
    });
    
    // Reset all forms
    document.querySelectorAll('form').forEach(form => {
        form.reset();
    });
    
    // Reset global variables
    editingMahasiswaId = null;
    editingSuratId = null;
    editingAkunId = null;
    currentSuratView = null;
    uploadedFiles = { images: [], documents: [] };
    
    // Reload all data
    loadDashboardData();
    
    showNotification('Sistem telah diperbaiki. Silakan coba lagi.', 'success');
    console.log('✅ System repair completed');
}

// ========== DEBUG FUNCTIONS ==========
window.debugDatabase = function() {
    console.group('🔍 DEBUG DATABASE');
    console.log('📊 Total Mahasiswa:', db.getAllMahasiswa().length);
    console.log('📊 Total Surat:', db.getAllSurat().length);
    console.log('📊 Total Pengguna:', db.getAllAkun().length);
    console.log('📋 Data Mahasiswa:', db.getAllMahasiswa());
    console.log('📋 Data Surat:', db.getAllSurat());
    console.log('👥 Data Pengguna:', db.getAllAkun());
    
    // Cek localStorage
    try {
        const mahasiswaLS = JSON.parse(localStorage.getItem('dataMahasiswa') || '[]');
        const penggunaLS = JSON.parse(localStorage.getItem('dataPengguna') || '[]');
        console.log('💾 LocalStorage - Mahasiswa:', mahasiswaLS.length, 'items');
        console.log('💾 LocalStorage - Pengguna:', penggunaLS.length, 'items');
    } catch (e) {
        console.error('❌ Error reading localStorage:', e);
    }
    
    console.groupEnd();
    showNotification('Debug info ditampilkan di console', 'info');
};

window.refreshMahasiswaTable = function() {
    console.log('🔄 Manually refreshing mahasiswa table');
    isiMahasiswa();
    isiDropdownMahasiswa();
    updateStats();
    showNotification('Tabel mahasiswa direfresh', 'success');
};

// ========== EMERGENCY REPAIR ==========
window.emergencyRepair = function() {
    if (confirm('PERBAIKAN DARURAT: Sistem akan direset ke kondisi default. Lanjutkan?')) {
        // Clear localStorage
        localStorage.removeItem('dataMahasiswa');
        localStorage.removeItem('riwayatSuratPeringatan');
        localStorage.removeItem('dataPengguna');
        localStorage.removeItem('pengaturanSistem');
        localStorage.removeItem('dashboardTheme');
        
        // Reload page
        location.reload();
    }
};

// ========== INITIALIZATION LOG ==========
console.log('🎉 admin.js loaded successfully!');
console.log('📊 Initial mahasiswa count:', db.getAllMahasiswa().length);