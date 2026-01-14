// admin-script.js - SIMPLE VERSION
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard loaded successfully!');
    initBasicFunctions();
});

function initBasicFunctions() {
    // Initialize clock
    updateClock();
    setInterval(updateClock, 1000);
    
    // Initialize date fields
    const today = new Date().toISOString().split('T')[0];
    const tanggalSurat = document.getElementById('tanggal_surat');
    const tanggalPelanggaran = document.getElementById('tanggal_pelanggaran');
    
    if (tanggalSurat) tanggalSurat.value = today;
    if (tanggalPelanggaran) tanggalPelanggaran.value = today;
    
    // Generate default nomor surat
    generateNomorSurat();
}

// Clock function
function updateClock() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateElement = document.getElementById('currentDate');
    const timeElement = document.getElementById('currentTime');
    
    if (dateElement) {
        dateElement.textContent = now.toLocaleDateString('id-ID', options);
    }
    if (timeElement) {
        timeElement.textContent = now.toLocaleTimeString('id-ID');
    }
}

// Sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');
    if (sidebar) sidebar.classList.toggle('show');
    if (overlay) overlay.classList.toggle('show');
}

// Navigation
function showDashboard() {
    const dashboardContent = document.getElementById('dashboardContent');
    const dynamicContent = document.getElementById('dynamicContent');
    const pageTitle = document.getElementById('pageTitle');
    
    if (dashboardContent) dashboardContent.style.display = 'block';
    if (dynamicContent) dynamicContent.style.display = 'none';
    if (pageTitle) pageTitle.textContent = 'Dashboard';
}

function loadPage(page) {
    const dashboardContent = document.getElementById('dashboardContent');
    const dynamicContent = document.getElementById('dynamicContent');
    const pageTitle = document.getElementById('pageTitle');
    
    if (dashboardContent) dashboardContent.style.display = 'none';
    if (dynamicContent) dynamicContent.style.display = 'block';
    
    const titles = {
        'arsip': 'Arsip Surat',
        'mahasiswa': 'Data Mahasiswa'
    };
    
    if (pageTitle && titles[page]) {
        pageTitle.textContent = titles[page];
    }
    
    // Show basic content for now
    const content = `<div class="p-4">
        <h5>Halaman ${titles[page]}</h5>
        <p>Fitur ini sedang dalam pengembangan.</p>
    </div>`;
    
    if (dynamicContent) {
        dynamicContent.innerHTML = content;
    }
}

// Modal functions
function showSuratModal() {
    const modal = new bootstrap.Modal(document.getElementById('suratModal'));
    modal.show();
}

function showTemplateModal() {
    const modal = new bootstrap.Modal(document.getElementById('templateModal'));
    modal.show();
}

function showMahasiswaModal() {
    const modal = new bootstrap.Modal(document.getElementById('mahasiswaModal'));
    modal.show();
}

function applyTemplate(template) {
    let keterangan = '';
    let sanksi = '';
    
    switch(template) {
        case 'akademik':
            keterangan = 'Mahasiswa tidak mengumpulkan tugas mata kuliah minimal 3 kali berturut-turut atau memiliki nilai di bawah standar yang ditetapkan.';
            sanksi = 'Peringatan tertulis pertama, wajib konsultasi dengan dosen pengampu, dan mengumpulkan semua tugas yang tertunggak dalam waktu 7 hari kerja.';
            break;
        case 'etika':
            keterangan = 'Mahasiswa melakukan pelanggaran etika seperti terlambat masuk kelas tanpa izin, tidak menghormati dosen atau teman sekelas, atau melakukan tindakan tidak terpuji lainnya.';
            sanksi = 'Peringatan tertulis, wajib membuat surat pernyataan, dan mengikuti sesi konseling dengan bagian kemahasiswaan.';
            break;
        case 'administrasi':
            keterangan = 'Mahasiswa tidak memenuhi kewajiban administratif seperti keterlambatan pembayaran SPP, tidak melengkapi dokumen administrasi, atau tidak mengikuti prosedur yang ditetapkan.';
            sanksi = 'Peringatan tertulis, dikenakan denda sesuai ketentuan, dan wajib melengkapi administrasi dalam waktu 3 hari kerja.';
            break;
        case 'umum':
            keterangan = 'Mahasiswa melanggar peraturan yang telah ditetapkan oleh institusi.';
            sanksi = 'Peringatan tertulis dan wajib mengikuti pembinaan.';
            break;
    }
    
    document.getElementById('keterangan').value = keterangan;
    document.getElementById('sanksi').value = sanksi;
    document.getElementById('jenis_pelanggaran').value = template === 'umum' ? 'lainnya' : template;
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('templateModal'));
    modal.hide();
    
    alert('Template berhasil diterapkan!');
}

function generateNomorSurat() {
    const currentYear = new Date().getFullYear();
    const randomNum = Math.floor(Math.random() * 100) + 1;
    const nomorSurat = document.getElementById('nomor_surat');
    
    if (nomorSurat) {
        nomorSurat.value = `SP/${currentYear}/${randomNum.toString().padStart(3, '0')}`;
    }
}

// Form submissions
function handleSuratSubmit(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const form = e.target;
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }
    
    alert('Surat berhasil disimpan! (Simulasi)');
    const modal = bootstrap.Modal.getInstance(document.getElementById('suratModal'));
    modal.hide();
    
    return false;
}

function handleMahasiswaSubmit(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const form = e.target;
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }
    
    alert('Data mahasiswa berhasil disimpan! (Simulasi)');
    const modal = bootstrap.Modal.getInstance(document.getElementById('mahasiswaModal'));
    modal.hide();
    
    return false;
}

// Preview functions
function previewSurat(id) {
    alert(`Preview surat ID: ${id} (Simulasi)`);
}

function printPreview() {
    window.print();
}

function downloadPreviewPDF() {
    alert('Download PDF (Simulasi)');
}

// Utility functions
function togglePasswordVisibility() {
    const passwordField = document.getElementById('password_mahasiswa');
    const button = document.querySelector('#mahasiswaModal .btn-outline-secondary i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        button.className = 'bi bi-eye-slash';
    } else {
        passwordField.type = 'password';
        button.className = 'bi bi-eye';
    }
}

function refreshRecentSurat() {
    alert('Refresh data (Simulasi)');
}

function logout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        window.location.href = 'logout.php';
    }
}

// Theme toggle
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('themeIcon');
    
    if (body.classList.contains('dark-mode')) {
        body.classList.remove('dark-mode');
        localStorage.setItem('theme', 'light');
        if (icon) icon.className = 'bi bi-moon';
    } else {
        body.classList.add('dark-mode');
        localStorage.setItem('theme', 'dark');
        if (icon) icon.className = 'bi bi-sun';
    }
}

// Initialize theme from localStorage
function initTheme() {
    const theme = localStorage.getItem('theme') || 'light';
    const icon = document.getElementById('themeIcon');
    
    if (theme === 'dark') {
        document.body.classList.add('dark-mode');
        if (icon) icon.className = 'bi bi-sun';
    }
}

// Make functions available globally
window.toggleSidebar = toggleSidebar;
window.showDashboard = showDashboard;
window.loadPage = loadPage;
window.showSuratModal = showSuratModal;
window.showTemplateModal = showTemplateModal;
window.showMahasiswaModal = showMahasiswaModal;
window.applyTemplate = applyTemplate;
window.handleSuratSubmit = handleSuratSubmit;
window.handleMahasiswaSubmit = handleMahasiswaSubmit;
window.previewSurat = previewSurat;
window.printPreview = printPreview;
window.downloadPreviewPDF = downloadPreviewPDF;
window.togglePasswordVisibility = togglePasswordVisibility;
window.refreshRecentSurat = refreshRecentSurat;
window.logout = logout;
window.toggleTheme = toggleTheme;