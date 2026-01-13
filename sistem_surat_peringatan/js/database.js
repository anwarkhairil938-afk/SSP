// DATABASE SIMULASI (dalam memori)
class Database {
    constructor() {
        this.loadFromStorage();
    }
    
    loadFromStorage() {
        console.log('📂 Loading data from storage...');
        
        // Load data dari localStorage
        const savedMahasiswa = localStorage.getItem('dataMahasiswa');
        const savedSurat = localStorage.getItem('riwayatSuratPeringatan');
        const savedPengguna = localStorage.getItem('dataPengguna');
        const savedPengaturan = localStorage.getItem('pengaturanSistem');
        
        // DATA MAHASISWA - Fix parsing error
        try {
            this.dataMahasiswa = savedMahasiswa ? JSON.parse(savedMahasiswa) : [
                {
                    id: 1,
                    nama: "M. Khairil Candra",
                    nim: "20220001",
                    programStudi: "Teknik Informatika",
                    semester: "5",
                    username: "mhs20220001",
                    password: "mhs123",
                    peran: "mahasiswa",
                    status: "aktif",
                    tanggalDibuat: new Date().toISOString(),
                    tanggalDiupdate: new Date().toISOString()
                },
                {
                    id: 2,
                    nama: "Qoonita Novia Damayanti",
                    nim: "20220002",
                    programStudi: "Sistem Informasi",
                    semester: "5",
                    username: "mhs20220002",
                    password: "mhs123",
                    peran: "mahasiswa",
                    status: "aktif",
                    tanggalDibuat: new Date().toISOString(),
                    tanggalDiupdate: new Date().toISOString()
                },
                {
                    id: 3,
                    nama: "Yoga Putra Agusetiawan",
                    nim: "20220003",
                    programStudi: "Teknik Elektro",
                    semester: "5",
                    username: "mhs20220003",
                    password: "mhs123",
                    peran: "mahasiswa",
                    status: "aktif",
                    tanggalDibuat: new Date().toISOString(),
                    tanggalDiupdate: new Date().toISOString()
                }
            ];
        } catch (e) {
            console.error('Error parsing mahasiswa data:', e);
            this.dataMahasiswa = [];
        }
        
        // DATA RIWAYAT SURAT PERINGATAN
        try {
            this.riwayatSuratPeringatan = savedSurat ? JSON.parse(savedSurat) : [
                {
                    id: 1,
                    nomorSurat: "SP/2023/001",
                    mahasiswaId: 1,
                    mahasiswa: "M. Khairil Candra",
                    nim: "20220001",
                    jenisPelanggaran: "akademik",
                    keterangan: "Tidak mengumpulkan tugas mata kuliah Pemrograman Web selama 3 minggu berturut-turut",
                    sanksi: "Peringatan tertulis dan wajib mengumpulkan semua tugas yang tertunggak dalam waktu 7 hari",
                    tanggalSurat: "2023-10-15",
                    tanggalPelanggaran: "2023-10-10",
                    penandatangan: "Dr. Surya Adi, M.Kom.",
                    status: "approved",
                    arsip: true,
                    lampiran: [],
                    tanggalDibuat: new Date().toISOString()
                },
                {
                    id: 2,
                    nomorSurat: "SP/2023/002",
                    mahasiswaId: 2,
                    mahasiswa: "Qoonita Novia Damayanti",
                    nim: "20220002",
                    jenisPelanggaran: "etika",
                    keterangan: "Terlambat mengikuti ujian tengah semester tanpa pemberitahuan sebelumnya",
                    sanksi: "Peringatan tertulis dan nilai ujian dikurangi 20%",
                    tanggalSurat: "2023-11-05",
                    tanggalPelanggaran: "2023-11-03",
                    penandatangan: "Dr. Rina Wijaya, M.T.",
                    status: "approved",
                    arsip: true,
                    lampiran: [],
                    tanggalDibuat: new Date().toISOString()
                },
                {
                    id: 3,
                    nomorSurat: "SP/2023/003",
                    mahasiswaId: 3,
                    mahasiswa: "Yoga Putra Agusetiawan",
                    nim: "20220003",
                    jenisPelanggaran: "administrasi",
                    keterangan: "Tidak melunasi biaya administrasi semester tepat waktu",
                    sanksi: "Peringatan tertulis dan denda keterlambatan 10% dari total biaya",
                    tanggalSurat: "2023-12-01",
                    tanggalPelanggaran: "2023-11-30",
                    penandatangan: "Dr. Budi Santoso, M.M.",
                    status: "pending",
                    arsip: false,
                    lampiran: [],
                    tanggalDibuat: new Date().toISOString()
                }
            ];
        } catch (e) {
            console.error('Error parsing surat data:', e);
            this.riwayatSuratPeringatan = [];
        }
        
        // DATA PENGATURAN
        try {
            this.pengaturanSistem = savedPengaturan ? JSON.parse(savedPengaturan) : {
                namaAplikasi: "Sistem Surat Peringatan Polibatam",
                versiAplikasi: "1.0.0",
                namaInstitusi: "Politeknik Negeri Batam",
                alamatInstitusi: "Jl. Ahmad Yani, Batam Kota, Batam, Kepulauan Riau 29461",
                telpInstitusi: "(0778) 469858",
                emailInstitusi: "polibatam@polibatam.ac.id",
                logoAplikasi: "",
                backupTerakhir: null,
                created: new Date().toISOString(),
                updated: new Date().toISOString()
            };
        } catch (e) {
            console.error('Error parsing pengaturan data:', e);
            this.pengaturanSistem = {};
        }
        
        // DATA PENGGUNA - Diperbaiki untuk sinkronisasi dengan mahasiswa
        try {
            const parsedPengguna = savedPengguna ? JSON.parse(savedPengguna) : [];
            
            // Pastikan ada admin utama
            const adminExists = parsedPengguna.find(p => p.peran === 'admin' && p.isAbsolute);
            
            if (!adminExists) {
                this.dataPengguna = [{
                    id: 1,
                    nama: "Admin Sistem",
                    username: "admin",
                    password: "admin123",
                    peran: "admin",
                    status: "aktif",
                    terakhirLogin: new Date().toISOString().slice(0, 19).replace('T', ' '),
                    isAbsolute: true
                }];
            } else {
                this.dataPengguna = parsedPengguna;
            }
            
            // Sinkronkan dengan data mahasiswa
            this.syncPenggunaWithMahasiswa();
            
        } catch (e) {
            console.error('Error parsing pengguna data:', e);
            this.dataPengguna = [{
                id: 1,
                nama: "Admin Sistem",
                username: "admin",
                password: "admin123",
                peran: "admin",
                status: "aktif",
                terakhirLogin: new Date().toISOString().slice(0, 19).replace('T', ' '),
                isAbsolute: true
            }];
            this.syncPenggunaWithMahasiswa();
        }
        
        console.log('✅ Data loaded successfully:', {
            mahasiswaCount: this.dataMahasiswa.length,
            suratCount: this.riwayatSuratPeringatan.length,
            penggunaCount: this.dataPengguna.length
        });
    }
    
    // Fungsi baru untuk sinkronisasi pengguna dengan mahasiswa
    syncPenggunaWithMahasiswa() {
        console.log('🔄 Syncing pengguna with mahasiswa...');
        
        // Tambahkan/update pengguna dari data mahasiswa
        this.dataMahasiswa.forEach(mahasiswa => {
            if (mahasiswa.status === 'aktif' || mahasiswa.status === 'cuti') {
                const existingUser = this.dataPengguna.find(p => 
                    p.peran === 'mahasiswa' && p.nim === mahasiswa.nim
                );
                
                if (!existingUser) {
                    const newUserId = this.dataPengguna.length > 0 ? 
                        Math.max(...this.dataPengguna.map(p => p.id)) + 1 : 1;
                    
                    this.dataPengguna.push({
                        id: newUserId,
                        nama: mahasiswa.nama,
                        username: mahasiswa.username,
                        password: mahasiswa.password,
                        peran: 'mahasiswa',
                        status: mahasiswa.status === 'cuti' ? 'aktif' : mahasiswa.status,
                        terakhirLogin: '-',
                        nim: mahasiswa.nim,
                        mahasiswaId: mahasiswa.id
                    });
                    
                    console.log(`➕ Added user for mahasiswa: ${mahasiswa.nama}`);
                }
            }
        });
    }
    
    saveToStorage() {
        try {
            // Simpan data dengan validasi
            localStorage.setItem('dataMahasiswa', JSON.stringify(this.dataMahasiswa));
            localStorage.setItem('riwayatSuratPeringatan', JSON.stringify(this.riwayatSuratPeringatan));
            localStorage.setItem('pengaturanSistem', JSON.stringify(this.pengaturanSistem));
            
            // Sinkronkan pengguna sebelum menyimpan
            this.syncPenggunaWithMahasiswa();
            localStorage.setItem('dataPengguna', JSON.stringify(this.dataPengguna));
            
            console.log('💾 Data saved to localStorage:', {
                mahasiswa: this.dataMahasiswa.length,
                surat: this.riwayatSuratPeringatan.length,
                pengguna: this.dataPengguna.length
            });
            return true;
        } catch (e) {
            console.error('❌ Error saving to localStorage:', e);
            return false;
        }
    }
    
    // ========== CRUD MAHASISWA ==========
    getMahasiswaById(id) {
        return this.dataMahasiswa.find(m => m.id === id);
    }
    
    getMahasiswaByNIM(nim) {
        return this.dataMahasiswa.find(m => m.nim === nim);
    }
    
    getMahasiswaByUsername(username) {
        return this.dataMahasiswa.find(m => m.username === username);
    }
    
    getAllMahasiswa() {
        return this.dataMahasiswa;
    }
    
    addMahasiswa(mahasiswa) {
        console.log('➕ Adding new mahasiswa:', mahasiswa);
        
        // Validasi data dengan pesan yang lebih jelas
        const requiredFields = ['nama', 'nim', 'username', 'password'];
        const missingFields = requiredFields.filter(field => !mahasiswa[field]);
        
        if (missingFields.length > 0) {
            console.error('❌ Data mahasiswa tidak lengkap. Field yang kurang:', missingFields);
            return { success: false, message: `Field ${missingFields.join(', ')} harus diisi` };
        }
        
        // Cek NIM duplikat
        const existingNIM = this.dataMahasiswa.find(m => m.nim === mahasiswa.nim);
        if (existingNIM) {
            console.error('❌ NIM sudah digunakan');
            return { success: false, message: 'NIM sudah digunakan' };
        }
        
        // Cek username duplikat
        const existingUsername = this.dataMahasiswa.find(m => m.username === mahasiswa.username);
        if (existingUsername) {
            console.error('❌ Username sudah digunakan');
            return { success: false, message: 'Username sudah digunakan' };
        }
        
        // Generate ID baru
        const newId = this.dataMahasiswa.length > 0 ? 
            Math.max(...this.dataMahasiswa.map(m => m.id)) + 1 : 1;
        
        // Buat objek mahasiswa lengkap
        const mahasiswaBaru = {
            id: newId,
            nama: mahasiswa.nama,
            nim: mahasiswa.nim,
            programStudi: mahasiswa.programStudi || 'Belum Ditentukan',
            semester: mahasiswa.semester || '1',
            username: mahasiswa.username,
            password: mahasiswa.password,
            peran: mahasiswa.peran || 'mahasiswa',
            status: mahasiswa.status || 'aktif',
            tanggalDibuat: new Date().toISOString(),
            tanggalDiupdate: new Date().toISOString()
        };
        
        // Tambahkan ke array
        this.dataMahasiswa.push(mahasiswaBaru);
        console.log('✅ Mahasiswa added. Total now:', this.dataMahasiswa.length);
        
        // Sinkronkan dengan pengguna
        this.syncPenggunaWithMahasiswa();
        
        // Simpan ke storage
        this.saveToStorage();
        
        return { success: true, data: mahasiswaBaru };
    }
    
    updateMahasiswa(id, data) {
        console.log('🔄 Updating mahasiswa ID:', id, 'with data:', data);
        
        const index = this.dataMahasiswa.findIndex(m => m.id === id);
        if (index === -1) {
            console.error('❌ Mahasiswa not found');
            return { success: false, message: 'Mahasiswa tidak ditemukan' };
        }
        
        const oldMahasiswa = this.dataMahasiswa[index];
        
        // Cek NIM duplikat (kecuali untuk diri sendiri)
        if (data.nim && data.nim !== oldMahasiswa.nim) {
            const existingNIM = this.dataMahasiswa.find(m => m.nim === data.nim && m.id !== id);
            if (existingNIM) {
                console.error('❌ NIM sudah digunakan');
                return { success: false, message: 'NIM sudah digunakan' };
            }
        }
        
        // Cek username duplikat (kecuali untuk diri sendiri)
        if (data.username && data.username !== oldMahasiswa.username) {
            const existingUsername = this.dataPengguna.find(p => 
                p.username === data.username && 
                p.peran === 'mahasiswa' && 
                p.nim !== oldMahasiswa.nim
            );
            if (existingUsername) {
                console.error('❌ Username sudah digunakan');
                return { success: false, message: 'Username sudah digunakan' };
            }
        }
        
        // Update data mahasiswa
        this.dataMahasiswa[index] = { 
            ...oldMahasiswa, 
            ...data,
            tanggalDiupdate: new Date().toISOString()
        };
        
        // Sinkronkan dengan pengguna
        this.syncPenggunaWithMahasiswa();
        
        this.saveToStorage();
        console.log('✅ Mahasiswa updated successfully');
        return { success: true };
    }
    
    deleteMahasiswa(id) {
        console.log('🗑️ Deleting mahasiswa ID:', id);
        
        const index = this.dataMahasiswa.findIndex(m => m.id === id);
        if (index === -1) {
            console.error('❌ Mahasiswa not found');
            return { success: false, message: 'Mahasiswa tidak ditemukan' };
        }
        
        const mahasiswa = this.dataMahasiswa[index];
        
        // Hapus dari data mahasiswa
        this.dataMahasiswa.splice(index, 1);
        
        // Hapus dari data pengguna jika ada
        const userIndex = this.dataPengguna.findIndex(p => 
            p.peran === 'mahasiswa' && p.nim === mahasiswa.nim
        );
        if (userIndex !== -1) {
            this.dataPengguna.splice(userIndex, 1);
        }
        
        this.saveToStorage();
        console.log('✅ Mahasiswa deleted. Total now:', this.dataMahasiswa.length);
        return { success: true };
    }
    
    // ========== CRUD SURAT PERINGATAN ==========
    getSuratById(id) {
        return this.riwayatSuratPeringatan.find(s => s.id === id);
    }
    
    getSuratByMahasiswaId(mahasiswaId) {
        return this.riwayatSuratPeringatan.filter(s => s.mahasiswaId === mahasiswaId);
    }
    
    getSuratByNIM(nim) {
        const mahasiswa = this.getMahasiswaByNIM(nim);
        if (mahasiswa) {
            return this.getSuratByMahasiswaId(mahasiswa.id);
        }
        return [];
    }
    
    getAllSurat() {
        return this.riwayatSuratPeringatan;
    }
    
    searchSurat(keyword, jenis = '', status = '') {
        return this.riwayatSuratPeringatan.filter(surat => {
            const matchesKeyword = 
                surat.mahasiswa.toLowerCase().includes(keyword.toLowerCase()) ||
                surat.nim.includes(keyword) ||
                surat.nomorSurat.toLowerCase().includes(keyword.toLowerCase());
            
            const matchesJenis = jenis === '' || surat.jenisPelanggaran === jenis;
            const matchesStatus = status === '' || surat.status === status;
            
            return matchesKeyword && matchesJenis && matchesStatus;
        });
    }
    
    addSurat(surat) {
        console.log('➕ Adding new surat:', surat);
        
        const newId = this.riwayatSuratPeringatan.length > 0 ? 
            Math.max(...this.riwayatSuratPeringatan.map(s => s.id)) + 1 : 1;
        
        const suratBaru = {
            id: newId,
            ...surat,
            tanggalDibuat: new Date().toISOString(),
            arsip: surat.status === 'approved'
        };
        
        this.riwayatSuratPeringatan.unshift(suratBaru);
        this.saveToStorage();
        
        console.log('✅ Surat added. Total now:', this.riwayatSuratPeringatan.length);
        return suratBaru;
    }
    
    updateSurat(id, data) {
        console.log('🔄 Updating surat ID:', id);
        
        const index = this.riwayatSuratPeringatan.findIndex(s => s.id === id);
        if (index === -1) {
            console.error('❌ Surat not found');
            return false;
        }
        
        this.riwayatSuratPeringatan[index] = { 
            ...this.riwayatSuratPeringatan[index], 
            ...data,
            arsip: data.status === 'approved' || this.riwayatSuratPeringatan[index].arsip
        };
        
        this.saveToStorage();
        console.log('✅ Surat updated');
        return true;
    }
    
    deleteSurat(id) {
        console.log('🗑️ Deleting surat ID:', id);
        
        const index = this.riwayatSuratPeringatan.findIndex(s => s.id === id);
        if (index === -1) {
            console.error('❌ Surat not found');
            return false;
        }
        
        this.riwayatSuratPeringatan.splice(index, 1);
        this.saveToStorage();
        
        console.log('✅ Surat deleted. Total now:', this.riwayatSuratPeringatan.length);
        return true;
    }
    
    // ========== PENGATURAN ==========
    getPengaturan() {
        return this.pengaturanSistem;
    }
    
    updatePengaturan(data) {
        this.pengaturanSistem = { 
            ...this.pengaturanSistem, 
            ...data, 
            updated: new Date().toISOString() 
        };
        this.saveToStorage();
        return true;
    }
    
    // ========== AKUN PENGGUNA ==========
    getAllAkun() {
        return this.dataPengguna;
    }
    
    getAkunById(id) {
        return this.dataPengguna.find(a => a.id === id);
    }
    
    addAkun(akun) {
        console.log('➕ Adding new akun:', akun);
        
        const newId = this.dataPengguna.length > 0 ? 
            Math.max(...this.dataPengguna.map(a => a.id)) + 1 : 1;
        
        const akunBaru = {
            id: newId,
            ...akun,
            terakhirLogin: '-'
        };
        
        this.dataPengguna.push(akunBaru);
        
        // Jika peran mahasiswa, tambahkan ke data mahasiswa
        if (akun.peran === 'mahasiswa') {
            const existingMahasiswa = this.dataMahasiswa.find(m => m.nim === akun.nim);
            if (!existingMahasiswa) {
                this.addMahasiswa({
                    nama: akun.nama,
                    nim: akun.nim,
                    programStudi: akun.programStudi || 'Belum Ditentukan',
                    semester: akun.semester || '1',
                    username: akun.username,
                    password: akun.password,
                    peran: akun.peran,
                    status: akun.status
                });
            }
        }
        
        this.saveToStorage();
        console.log('✅ Akun added');
        return akunBaru;
    }
    
    updateAkun(id, data) {
        console.log('🔄 Updating akun ID:', id);
        
        const index = this.dataPengguna.findIndex(a => a.id === id);
        if (index === -1) {
            console.error('❌ Akun not found');
            return false;
        }
        
        this.dataPengguna[index] = { ...this.dataPengguna[index], ...data };
        
        // Update juga di data mahasiswa jika peran mahasiswa
        if (this.dataPengguna[index].peran === 'mahasiswa') {
            const mahasiswaIndex = this.dataMahasiswa.findIndex(m => m.nim === this.dataPengguna[index].nim);
            if (mahasiswaIndex !== -1) {
                this.dataMahasiswa[mahasiswaIndex] = {
                    ...this.dataMahasiswa[mahasiswaIndex],
                    nama: data.nama || this.dataPengguna[index].nama,
                    nim: data.nim || this.dataPengguna[index].nim,
                    username: data.username || this.dataPengguna[index].username,
                    password: data.password || this.dataPengguna[index].password,
                    status: data.status === 'aktif' ? 'aktif' : 'nonaktif'
                };
            }
        }
        
        this.saveToStorage();
        console.log('✅ Akun updated');
        return true;
    }
    
    deleteAkun(id) {
        console.log('🗑️ Deleting akun ID:', id);
        
        const index = this.dataPengguna.findIndex(a => a.id === id);
        if (index === -1) {
            console.error('❌ Akun not found');
            return false;
        }
        
        const akun = this.dataPengguna[index];
        
        // Jangan izinkan hapus akun admin utama
        if (akun.isAbsolute) {
            console.error('❌ Cannot delete absolute admin account');
            return false;
        }
        
        this.dataPengguna.splice(index, 1);
        
        // Jika peran mahasiswa, hapus dari data mahasiswa
        if (akun.peran === 'mahasiswa') {
            const mahasiswaIndex = this.dataMahasiswa.findIndex(m => m.nim === akun.nim);
            if (mahasiswaIndex !== -1) {
                this.dataMahasiswa.splice(mahasiswaIndex, 1);
            }
        }
        
        this.saveToStorage();
        console.log('✅ Akun deleted');
        return true;
    }
    
    // ========== BACKUP & RESTORE ==========
    backupDatabase() {
        const backupData = {
            timestamp: new Date().toISOString(),
            dataMahasiswa: this.dataMahasiswa,
            riwayatSuratPeringatan: this.riwayatSuratPeringatan,
            dataPengguna: this.dataPengguna,
            pengaturanSistem: this.pengaturanSistem
        };
        
        this.pengaturanSistem.backupTerakhir = new Date().toISOString();
        this.saveToStorage();
        
        console.log('💾 Backup created');
        return backupData;
    }
    
    restoreDatabase(backupData) {
        console.log('🔄 Restoring database from backup');
        
        this.dataMahasiswa = backupData.dataMahasiswa || [];
        this.riwayatSuratPeringatan = backupData.riwayatSuratPeringatan || [];
        this.dataPengguna = backupData.dataPengguna || [];
        this.pengaturanSistem = backupData.pengaturanSistem || this.pengaturanSistem;
        
        this.saveToStorage();
        console.log('✅ Database restored');
        return true;
    }
    
    resetDatabase() {
        console.log('🔄 Resetting database');
        
        // Simpan hanya akun admin utama
        const adminAkun = this.dataPengguna.find(a => a.peran === 'admin' && a.isAbsolute);
        
        this.dataMahasiswa = [];
        this.riwayatSuratPeringatan = [];
        this.dataPengguna = adminAkun ? [adminAkun] : [];
        this.pengaturanSistem = {
            namaAplikasi: "Sistem Surat Peringatan Polibatam",
            versiAplikasi: "1.0.0",
            namaInstitusi: "Politeknik Negeri Batam",
            alamatInstitusi: "Jl. Ahmad Yani, Batam Kota, Batam, Kepulauan Riau 29461",
            telpInstitusi: "(0778) 469858",
            emailInstitusi: "polibatam@polibatam.ac.id",
            logoAplikasi: "",
            backupTerakhir: null,
            created: new Date().toISOString(),
            updated: new Date().toISOString()
        };
        
        this.saveToStorage();
        console.log('✅ Database reset');
        return true;
    }
}

// VARIABEL GLOBAL
let db = new Database();
let currentUser = null;
let isDarkTheme = false;
let editingMahasiswaId = null;
let editingSuratId = null;
let currentSuratView = null;
let editingAkunId = null;

// Untuk file upload
let uploadedFiles = {
    images: [],
    documents: []
};