// Mahasiswa Module
class MahasiswaModule {
    constructor() {
        this.mahasiswaModal = new bootstrap.Modal(document.getElementById('mahasiswaModal'));
    }
    
    showModal(mahasiswaId = null) {
        const form = document.getElementById('formMahasiswa');
        form.reset();
        form.classList.remove('was-validated');
        
        if (mahasiswaId) {
            document.getElementById('mahasiswaModalTitle').textContent = 'Edit Data Mahasiswa';
            document.getElementById('mahasiswa_id_edit').value = mahasiswaId;
            document.getElementById('passwordRequired').style.display = 'none';
            this.loadData(mahasiswaId);
        } else {
            document.getElementById('mahasiswaModalTitle').textContent = 'Tambah Mahasiswa';
            document.getElementById('mahasiswa_id_edit').value = '';
            document.getElementById('passwordRequired').style.display = 'inline';
            document.getElementById('password_mahasiswa').required = true;
        }
        
        this.mahasiswaModal.show();
    }
    
    loadData(id) {
        showLoading(true);
        fetch(`api/get_mahasiswa.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const mhs = data.data;
                    document.getElementById('nama_mahasiswa').value = mhs.nama;
                    document.getElementById('nim_mahasiswa').value = mhs.nim;
                    document.getElementById('prodi_mahasiswa').value = mhs.program_studi;
                    document.getElementById('semester_mahasiswa').value = mhs.semester;
                    document.getElementById('username_mahasiswa').value = mhs.username;
                    document.getElementById('status_mahasiswa').value = mhs.status;
                } else {
                    showAlert('Error', data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('Error', 'Gagal memuat data mahasiswa', 'error');
            })
            .finally(() => showLoading(false));
    }
    
    handleSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const isEdit = document.getElementById('mahasiswa_id_edit').value !== '';
        
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        // Show loading on button
        const submitBtn = document.getElementById('mahasiswaSubmitBtn');
        const spinner = document.getElementById('mahasiswaSpinner');
        const btnText = document.getElementById('mahasiswaBtnText');
        
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Menyimpan...';
        
        // Prepare form data
        const formData = new FormData(form);
        const url = isEdit ? 'api/update_mahasiswa.php' : 'api/tambah_mahasiswa.php';
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Berhasil!', data.message, 'success');
                this.mahasiswaModal.hide();
                refreshDashboard();
                if (document.getElementById('dynamicContent').style.display === 'block') {
                    const activeNav = document.querySelector('.nav-link.active');
                    if (activeNav && activeNav.textContent.includes('Mahasiswa')) {
                        loadMahasiswaContent();
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
            btnText.textContent = 'Simpan';
        });
    }
    
    hapus(id) {
        Swal.fire({
            title: 'Hapus Mahasiswa?',
            text: 'Data mahasiswa akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading(true);
                fetch('api/hapus_mahasiswa.php', {
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
                            loadMahasiswaDataAll();
                        }
                    } else {
                        showAlert('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    showAlert('Error', 'Gagal menghapus mahasiswa', 'error');
                })
                .finally(() => showLoading(false));
            }
        });
    }
    
    togglePasswordVisibility() {
        const passwordField = document.getElementById('password_mahasiswa');
        const icon = document.querySelector('#mahasiswaModal .btn-outline-secondary i');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            passwordField.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }
}

// Create instance
const mahasiswaModule = new MahasiswaModule();