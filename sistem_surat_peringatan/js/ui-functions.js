// ========== UI FUNCTIONS ==========
function isiMahasiswa(filters = {}) {
    console.log('🔄 Loading mahasiswa table...');
    
    const tbody = document.getElementById('mahasiswaTableBody');
    if (!tbody) {
        console.error('❌ mahasiswaTableBody element not found!');
        return;
    }
    
    tbody.innerHTML = '';
    
    let mahasiswaUntukDitampilkan = db.getAllMahasiswa();
    
    console.log('📊 Found', mahasiswaUntukDitampilkan.length, 'mahasiswa');
    
    // Apply filters
    if (filters.nama || filters.nim || filters.prodi) {
        mahasiswaUntukDitampilkan = mahasiswaUntukDitampilkan.filter(mahasiswa => {
            const matchesNama = !filters.nama || 
                mahasiswa.nama.toLowerCase().includes(filters.nama.toLowerCase());
            const matchesNIM = !filters.nim || 
                mahasiswa.nim.includes(filters.nim);
            const matchesProdi = !filters.prodi || 
                mahasiswa.programStudi === filters.prodi;
            
            return matchesNama && matchesNIM && matchesProdi;
        });
        console.log('🔍 Filtered to', mahasiswaUntukDitampilkan.length, 'mahasiswa');
    }
    
    if (mahasiswaUntukDitampilkan.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                    ${filters.nama || filters.nim || filters.prodi ? 
                      'Tidak ditemukan data yang sesuai dengan filter' : 
                      'Belum ada data mahasiswa'}
                </td>
            </tr>
        `;
        console.log('📭 No mahasiswa data to display');
        return;
    }
    
    mahasiswaUntukDitampilkan.forEach(mahasiswa => {
        const tr = document.createElement('tr');
        
        const statusClass = mahasiswa.status === 'aktif' ? 'status-approved' : 
                          mahasiswa.status === 'nonaktif' ? 'status-rejected' : 'status-pending';
        const statusText = mahasiswa.status === 'aktif' ? 'Aktif' : 
                         mahasiswa.status === 'nonaktif' ? 'Nonaktif' : 'Cuti';
        
        tr.innerHTML = `
            <td>${mahasiswa.nama}</td>
            <td>${mahasiswa.nim}</td>
            <td>${mahasiswa.programStudi}</td>
            <td>${mahasiswa.semester}</td>
            <td>${mahasiswa.username}</td>
            <td>${mahasiswa.peran === 'mahasiswa' ? 'Mahasiswa' : 'Admin'}</td>
            <td><span class="letter-status ${statusClass}">${statusText}</span></td>
            <td>
                <button class="btn btn-warning btn-sm" onclick="editMahasiswa(${mahasiswa.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="hapusMahasiswa(${mahasiswa.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
    
    console.log('✅ Mahasiswa table loaded with', mahasiswaUntukDitampilkan.length, 'items');
}

function isiDropdownMahasiswa() {
    console.log('🔄 Loading mahasiswa dropdown...');
    
    const select = document.getElementById('mahasiswa');
    if (!select) {
        console.error('❌ Mahasiswa dropdown element not found!');
        return;
    }
    
    select.innerHTML = '<option value="">Pilih Mahasiswa</option>';
    
    const mahasiswaAktif = db.getAllMahasiswa().filter(m => m.status === 'aktif');
    console.log('📊 Found', mahasiswaAktif.length, 'active mahasiswa for dropdown');
    
    mahasiswaAktif.forEach(mahasiswa => {
        const option = document.createElement('option');
        option.value = mahasiswa.id;
        option.textContent = `${mahasiswa.nama} (${mahasiswa.nim})`;
        select.appendChild(option);
    });
    
    console.log('✅ Mahasiswa dropdown loaded');
}

function isiSuratTerbaru() {
    console.log('🔄 Loading latest surat...');
    
    const list = document.getElementById('suratTerbaruList');
    if (!list) {
        console.error('❌ suratTerbaruList element not found!');
        return;
    }
    
    list.innerHTML = '';
    
    const suratTerbaru = db.getAllSurat().slice(0, 5);
    
    if (suratTerbaru.length === 0) {
        list.innerHTML = `
            <li class="letter-item">
                <div style="text-align: center; padding: 20px; color: var(--text-secondary);">
                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p>Belum ada surat peringatan</p>
                </div>
            </li>
        `;
        return;
    }
    
    suratTerbaru.forEach(surat => {
        const li = document.createElement('li');
        li.className = 'letter-item';
        li.style.cursor = 'pointer';
        li.onclick = () => tampilkanSurat(surat.id);
        
        const statusClass = surat.status === 'approved' ? 'status-approved' : 
                          surat.status === 'rejected' ? 'status-rejected' : 'status-pending';
        
        li.innerHTML = `
            <div>
                <div class="letter-title">${surat.nomorSurat}</div>
                <div class="letter-meta">${surat.mahasiswa} • ${surat.nim}</div>
            </div>
            <span class="letter-status ${statusClass}">${surat.status === 'approved' ? 'Disetujui' : surat.status === 'rejected' ? 'Ditolak' : 'Menunggu'}</span>
        `;
        
        list.appendChild(li);
    });
    
    console.log('✅ Latest surat loaded');
}

function refreshSuratTerbaru() {
    const list = document.getElementById('suratTerbaruList');
    if (list) {
        list.innerHTML = '<li class="letter-item"><div class="loading"><i class="fas fa-spinner"></i> Memuat data...</div></li>';
    }
    
    setTimeout(() => {
        isiSuratTerbaru();
        updateStats();
        showNotification('Data berhasil direfresh', 'success');
    }, 500);
}

function isiRiwayat(filters = {}) {
    console.log('🔄 Loading riwayat table...');
    
    const tbody = document.getElementById('riwayatTableBody');
    if (!tbody) {
        console.error('❌ riwayatTableBody element not found!');
        return;
    }
    
    tbody.innerHTML = '';
    
    let suratUntukDitampilkan = db.getAllSurat();
    
    // Apply filters
    if (filters.keyword || filters.jenis || filters.status) {
        const keyword = filters.keyword || '';
        const jenis = filters.jenis || '';
        const status = filters.status || '';
        suratUntukDitampilkan = db.searchSurat(keyword, jenis, status);
    }
    
    if (suratUntukDitampilkan.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                    Tidak ditemukan data yang sesuai
                </td>
            </tr>
        `;
        return;
    }
    
    suratUntukDitampilkan.forEach(surat => {
        const tr = document.createElement('tr');
        
        const statusClass = surat.status === 'approved' ? 'status-approved' : 
                          surat.status === 'rejected' ? 'status-rejected' : 'status-pending';
        const statusText = surat.status === 'approved' ? 'Disetujui' : 
                         surat.status === 'rejected' ? 'Ditolak' : 'Menunggu';
        
        tr.innerHTML = `
            <td>${surat.nomorSurat}</td>
            <td>${surat.mahasiswa}<br><small>${surat.nim}</small></td>
            <td>${surat.jenisPelanggaran === 'akademik' ? 'Akademik' : 
                 surat.jenisPelanggaran === 'etika' ? 'Etika' : 
                 surat.jenisPelanggaran === 'administrasi' ? 'Administrasi' : 'Lainnya'}</td>
            <td>${formatTanggalIndonesia(surat.tanggalSurat)}</td>
            <td>${formatTanggalIndonesia(surat.tanggalPelanggaran)}</td>
            <td><span class="letter-status ${statusClass}">${statusText}</span></td>
            <td>
                <button class="btn btn-primary btn-sm" onclick="tampilkanSurat(${surat.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-warning btn-sm" onclick="editSurat(${surat.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="hapusSurat(${surat.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}

function isiArsip(filters = {}) {
    console.log('🔄 Loading arsip table...');
    
    const tbody = document.getElementById('arsipTableBody');
    if (!tbody) {
        console.error('❌ arsipTableBody element not found!');
        return;
    }
    
    tbody.innerHTML = '';
    
    let suratUntukDitampilkan = db.getAllSurat().filter(s => s.arsip === true);
    
    // Apply filters
    if (filters.nama || filters.nim || filters.jenis) {
        suratUntukDitampilkan = suratUntukDitampilkan.filter(surat => {
            const matchesNama = !filters.nama || 
                surat.mahasiswa.toLowerCase().includes(filters.nama.toLowerCase());
            const matchesNIM = !filters.nim || 
                surat.nim.includes(filters.nim);
            const matchesJenis = !filters.jenis || 
                surat.jenisPelanggaran === filters.jenis;
            
            return matchesNama && matchesNIM && matchesJenis;
        });
    }
    
    if (suratUntukDitampilkan.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                    ${filters.nama || filters.nim || filters.jenis ? 
                      'Tidak ditemukan data yang sesuai dengan filter' : 
                      'Belum ada surat yang diarsipkan'}
                </td>
            </tr>
        `;
        return;
    }
    
    suratUntukDitampilkan.forEach(surat => {
        const tr = document.createElement('tr');
        
        const statusClass = surat.status === 'approved' ? 'status-approved' : 
                          surat.status === 'rejected' ? 'status-rejected' : 'status-pending';
        const statusText = surat.status === 'approved' ? 'Disetujui' : 
                         surat.status === 'rejected' ? 'Ditolak' : 'Menunggu';
        
        tr.innerHTML = `
            <td>${surat.nomorSurat}</td>
            <td>${surat.mahasiswa}</td>
            <td>${surat.nim}</td>
            <td>${surat.jenisPelanggaran === 'akademik' ? 'Akademik' : 
                 surat.jenisPelanggaran === 'etika' ? 'Etika' : 
                 surat.jenisPelanggaran === 'administrasi' ? 'Administrasi' : 'Lainnya'}</td>
            <td>${formatTanggalIndonesia(surat.tanggalSurat)}</td>
            <td><span class="letter-status ${statusClass}">${statusText}</span></td>
            <td>
                <button class="btn btn-primary btn-sm" onclick="tampilkanSurat(${surat.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-warning btn-sm" onclick="editSurat(${surat.id})">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}

function isiAkunTable() {
    console.log('🔄 Loading akun table...');
    
    const tbody = document.getElementById('akunTableBody');
    if (!tbody) {
        console.error('❌ akunTableBody element not found!');
        return;
    }
    
    tbody.innerHTML = '';
    
    const akunList = db.getAllAkun();
    
    if (akunList.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                    Belum ada data akun
                </td>
            </tr>
        `;
        return;
    }
    
    akunList.forEach(akun => {
        const tr = document.createElement('tr');
        
        const statusClass = akun.status === 'aktif' ? 'status-approved' : 'status-rejected';
        const statusText = akun.status === 'aktif' ? 'Aktif' : 'Nonaktif';
        const roleText = akun.peran === 'admin' ? 'Administrator' : 'Mahasiswa';
        
        tr.innerHTML = `
            <td>${akun.username}</td>
            <td>${akun.nama}</td>
            <td>${roleText}</td>
            <td><span class="letter-status ${statusClass}">${statusText}</span></td>
            <td>${akun.terakhirLogin || '-'}</td>
            <td>
                <button class="btn btn-warning btn-sm" onclick="editAkun(${akun.id})">
                    <i class="fas fa-edit"></i>
                </button>
                ${!akun.isAbsolute ? `
                <button class="btn btn-danger btn-sm" onclick="hapusAkun(${akun.id})">
                    <i class="fas fa-trash"></i>
                </button>
                ` : ''}
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}

// ========== UTILITY FUNCTIONS ==========
function updateStats() {
    console.log('📈 Updating statistics...');
    
    const totalSurat = db.getAllSurat().length;
    const suratDisetujui = db.getAllSurat().filter(s => s.status === 'approved').length;
    const menunggu = db.getAllSurat().filter(s => s.status === 'pending').length;
    const totalMahasiswa = db.getAllMahasiswa().filter(m => m.status === 'aktif').length;
    
    console.log('📊 Stats:', {
        totalSurat,
        suratDisetujui,
        menunggu,
        totalMahasiswa
    });
    
    // Update stat cards
    const statElements = {
        'statTotalSurat': totalSurat,
        'statSuratDisetujui': suratDisetujui,
        'statMenunggu': menunggu,
        'statTotalMahasiswa': totalMahasiswa
    };
    
    Object.keys(statElements).forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            animateNumber(element, parseInt(element.textContent) || 0, statElements[elementId], 800);
        }
    });
    
    // Update stat cards di welcome section
    const welcomeStats = {
        'statLetters': totalSurat,
        'statPending': menunggu,
        'statUsers': totalMahasiswa
    };
    
    Object.keys(welcomeStats).forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            animateNumber(element, parseInt(element.textContent) || 0, welcomeStats[elementId], 800);
        }
    });
}

function animateNumber(element, start, end, duration) {
    if (!element) return;
    
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value;
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// ========== NAVIGATION FUNCTIONS ==========
function showDashboardMain() {
    hideAllPages();
    document.getElementById('dashboardMain').style.display = 'block';
    document.getElementById('breadcrumb').textContent = 'Dashboard';
    updateActiveNav('navDashboard');
}

function showRiwayatPage() {
    hideAllPages();
    document.getElementById('riwayatPage').style.display = 'block';
    document.getElementById('breadcrumb').textContent = 'Riwayat Surat';
    updateActiveNav('navRiwayat');
    
    // Refresh data saat membuka halaman
    isiRiwayat();
    resetFilterRiwayat();
}

function showArsipPage() {
    hideAllPages();
    document.getElementById('arsipPage').style.display = 'block';
    document.getElementById('breadcrumb').textContent = 'Arsip Surat';
    updateActiveNav('navArsipLink');
    
    // Refresh data saat membuka halaman
    isiArsip();
    resetFilterArsip();
}

function showDataMahasiswaPage() {
    hideAllPages();
    document.getElementById('dataMahasiswaPage').style.display = 'block';
    document.getElementById('breadcrumb').textContent = 'Data Mahasiswa';
    updateActiveNav('navDataMahasiswaLink');
    
    // Refresh data saat membuka halaman
    isiMahasiswa();
    resetFilterMahasiswa();
}

function showPengaturanPage() {
    hideAllPages();
    document.getElementById('pengaturanPage').style.display = 'block';
    document.getElementById('breadcrumb').textContent = 'Pengaturan Sistem';
    updateActiveNav('navPengaturan');
    
    // Load pengaturan data
    loadPengaturanData();
    isiAkunTable();
}

function hideAllPages() {
    const pages = ['dashboardMain', 'riwayatPage', 'arsipPage', 'dataMahasiswaPage', 'pengaturanPage'];
    pages.forEach(pageId => {
        const page = document.getElementById(pageId);
        if (page) {
            page.style.display = 'none';
        }
    });
}

function updateActiveNav(navId) {
    // Remove active class from all nav links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // Add active class to current nav
    const activeNav = document.getElementById(navId);
    if (activeNav) {
        activeNav.classList.add('active');
    }
}

// ========== UI FUNCTIONS ==========
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
    if (overlay) {
        overlay.classList.toggle('active');
    }
}

function toggleTheme() {
    const themeToggle = document.getElementById('themeToggle');
    isDarkTheme = !isDarkTheme;
    
    if (isDarkTheme) {
        document.body.classList.add('dark-theme');
        if (themeToggle) {
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        localStorage.setItem('dashboardTheme', 'dark');
    } else {
        document.body.classList.remove('dark-theme');
        if (themeToggle) {
            themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        }
        localStorage.setItem('dashboardTheme', 'light');
    }
}

function updateClock() {
    const now = new Date();
    const dateElement = document.getElementById('currentDate');
    const timeElement = document.getElementById('currentTime');
    
    if (dateElement && timeElement) {
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        dateElement.textContent = now.toLocaleDateString('id-ID', options);
        timeElement.textContent = now.toLocaleTimeString('id-ID');
    }
}

// ========== FILTER FUNCTIONS ==========
function filterMahasiswa() {
    const nama = document.getElementById('cariNamaMahasiswa')?.value || '';
    const nim = document.getElementById('cariNIMMahasiswa')?.value || '';
    const prodi = document.getElementById('filterProdiMahasiswa')?.value || '';
    
    console.log('🔍 Filtering mahasiswa:', { nama, nim, prodi });
    isiMahasiswa({ nama, nim, prodi });
}

function resetFilterMahasiswa() {
    document.getElementById('cariNamaMahasiswa') && (document.getElementById('cariNamaMahasiswa').value = '');
    document.getElementById('cariNIMMahasiswa') && (document.getElementById('cariNIMMahasiswa').value = '');
    document.getElementById('filterProdiMahasiswa') && (document.getElementById('filterProdiMahasiswa').value = '');
    
    console.log('🔄 Resetting mahasiswa filter');
    isiMahasiswa();
}

function filterRiwayat() {
    const keyword = document.getElementById('cariNamaRiwayat')?.value || '';
    const jenis = document.getElementById('filterJenisRiwayat')?.value || '';
    const status = document.getElementById('filterStatusRiwayat')?.value || '';
    
    console.log('🔍 Filtering riwayat:', { keyword, jenis, status });
    isiRiwayat({ keyword, jenis, status });
}

function resetFilterRiwayat() {
    document.getElementById('cariNamaRiwayat') && (document.getElementById('cariNamaRiwayat').value = '');
    document.getElementById('filterJenisRiwayat') && (document.getElementById('filterJenisRiwayat').value = '');
    document.getElementById('filterStatusRiwayat') && (document.getElementById('filterStatusRiwayat').value = '');
    
    console.log('🔄 Resetting riwayat filter');
    isiRiwayat();
}

function filterArsip() {
    const nama = document.getElementById('cariNamaArsip')?.value || '';
    const nim = document.getElementById('cariNIMArsip')?.value || '';
    const jenis = document.getElementById('filterJenisArsip')?.value || '';
    
    console.log('🔍 Filtering arsip:', { nama, nim, jenis });
    isiArsip({ nama, nim, jenis });
}

function resetFilterArsip() {
    document.getElementById('cariNamaArsip') && (document.getElementById('cariNamaArsip').value = '');
    document.getElementById('cariNIMArsip') && (document.getElementById('cariNIMArsip').value = '');
    document.getElementById('filterJenisArsip') && (document.getElementById('filterJenisArsip').value = '');
    
    console.log('🔄 Resetting arsip filter');
    isiArsip();
}

// ========== CRUD FUNCTIONS (untuk onclick di table) ==========
window.tampilkanSurat = function(id) {
    const surat = db.getSuratById(id);
    if (!surat) {
        showNotification('Surat tidak ditemukan!', 'error');
        return;
    }
    
    const mahasiswa = db.getMahasiswaById(surat.mahasiswaId);
    const suratContent = document.getElementById('suratContent');
    
    if (suratContent) {
        const suratHTML = buatTemplateSurat(surat, mahasiswa);
        suratContent.innerHTML = suratHTML;
        
        // Simpan surat untuk download PDF
        currentSuratView = {
            ...surat,
            programStudi: mahasiswa?.programStudi,
            semester: mahasiswa?.semester
        };
        
        // Tampilkan lampiran jika ada
        if (surat.lampiran && surat.lampiran.length > 0) {
            const lampiranHTML = `
                <div style="margin-top: 20px; padding: 15px; background: var(--light); border-radius: var(--border-radius);">
                    <h5 style="margin-bottom: 10px;"><i class="fas fa-paperclip"></i> Lampiran (${surat.lampiran.length} file)</h5>
                    <ul style="padding-left: 20px;">
                        ${surat.lampiran.map(file => 
                            `<li>${file.name} (${formatFileSize(file.size)}) - ${file.type === 'image' ? 'Gambar' : 'Dokumen'}</li>`
                        ).join('')}
                    </ul>
                </div>
            `;
            suratContent.insertAdjacentHTML('beforeend', lampiranHTML);
        }
    }
    
    showModal('tampilanSuratModal');
};

window.editSurat = function(id) {
    showSuratModal(id);
};

window.hapusSurat = function(id) {
    const surat = db.getSuratById(id);
    if (!surat) {
        showNotification('Surat tidak ditemukan!', 'error');
        return;
    }
    
    // Show confirmation modal
    document.getElementById('confirmModalTitle').textContent = 'Hapus Surat Peringatan';
    document.getElementById('confirmModalMessage').textContent = `Apakah Anda yakin ingin menghapus surat ${surat.nomorSurat}?`;
    document.getElementById('confirmAction').textContent = 'Ya, Hapus';
    
    // Set callback untuk confirm
    const confirmBtn = document.getElementById('confirmAction');
    if (confirmBtn) {
        confirmBtn.onclick = function() {
            const success = db.deleteSurat(id);
            if (success) {
                showNotification('Surat peringatan berhasil dihapus!', 'success');
                isiSuratTerbaru();
                isiRiwayat();
                isiArsip();
                updateStats();
                hideModal('confirmModal');
            } else {
                showNotification('Gagal menghapus surat peringatan!', 'error');
            }
        };
    }
    
    showModal('confirmModal');
};

window.editMahasiswa = function(id) {
    console.log('✏️ Edit mahasiswa clicked:', id);
    showTambahMahasiswaModal(id);
};

window.hapusMahasiswa = function(id) {
    const mahasiswa = db.getMahasiswaById(id);
    if (!mahasiswa) {
        showNotification('Mahasiswa tidak ditemukan!', 'error');
        return;
    }
    
    // Show confirmation modal
    document.getElementById('confirmModalTitle').textContent = 'Hapus Mahasiswa';
    document.getElementById('confirmModalMessage').textContent = `Apakah Anda yakin ingin menghapus mahasiswa ${mahasiswa.nama} (${mahasiswa.nim})?`;
    document.getElementById('confirmAction').textContent = 'Ya, Hapus';
    
    // Set callback untuk confirm
    const confirmBtn = document.getElementById('confirmAction');
    if (confirmBtn) {
        confirmBtn.onclick = function() {
            const success = db.deleteMahasiswa(id);
            if (success) {
                showNotification(`Mahasiswa ${mahasiswa.nama} berhasil dihapus!`, 'success');
                isiMahasiswa();
                isiDropdownMahasiswa();
                updateStats();
                hideModal('confirmModal');
            } else {
                showNotification('Gagal menghapus mahasiswa!', 'error');
            }
        };
    }
    
    showModal('confirmModal');
};

window.editAkun = function(id) {
    console.log('✏️ Edit akun clicked:', id);
    const akun = db.getAkunById(id);
    if (!akun) {
        showNotification('Akun tidak ditemukan!', 'error');
        return;
    }
    
    // Set form values
    document.getElementById('usernameAkun').value = akun.username || '';
    document.getElementById('namaAkun').value = akun.nama || '';
    document.getElementById('passwordAkun').value = akun.password || '';
    document.getElementById('roleAkun').value = akun.peran || '';
    document.getElementById('statusAkun').value = akun.status || 'aktif';
    document.getElementById('nimAkun').value = akun.nim || '';
    
    editingAkunId = id;
    
    // Scroll to form
    document.getElementById('formTambahAkun').scrollIntoView({ behavior: 'smooth' });
};

window.hapusAkun = function(id) {
    const akun = db.getAkunById(id);
    if (!akun) {
        showNotification('Akun tidak ditemukan!', 'error');
        return;
    }
    
    if (confirm(`Apakah Anda yakin ingin menghapus akun ${akun.username}?`)) {
        const success = db.deleteAkun(id);
        if (success) {
            showNotification(`Akun ${akun.username} berhasil dihapus!`, 'success');
            isiAkunTable();
            isiMahasiswa();
            updateStats();
        } else {
            showNotification('Gagal menghapus akun!', 'error');
        }
    }
};

// Helper function untuk format tanggal
function formatTanggalIndonesia(dateString) {
    const date = new Date(dateString);
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
}