// ========== PDF FUNCTIONS ==========
function setupPDFLibrary() {
    // Load jsPDF library jika belum ada
    if (typeof window.jspdf === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
        script.onload = function() {
            console.log('✅ jsPDF library loaded');
        };
        document.head.appendChild(script);
    }
}

function downloadSuratPDF() {
    console.log('📄 Generating PDF...');
    
    if (!currentSuratView) {
        showNotification('Tidak ada surat untuk di-download!', 'error');
        return;
    }
    
    showNotification('Membuat PDF...', 'info');
    
    try {
        // Coba gunakan jsPDF jika tersedia
        if (typeof window.jspdf !== 'undefined' && window.jspdf.jsPDF) {
            generatePDFWithjsPDF();
        } else {
            // Fallback ke html2pdf jika jsPDF tidak tersedia
            generatePDFWithHTML2PDF();
        }
    } catch (error) {
        console.error('Error generating PDF:', error);
        showNotification('Gagal membuat PDF. Silakan coba lagi.', 'error');
    }
}

function generatePDFWithjsPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Set font dan ukuran
    doc.setFont("helvetica", "normal");
    doc.setFontSize(12);
    
    const pengaturan = db.getPengaturan();
    const surat = currentSuratView;
    
    // Header
    doc.setFontSize(16);
    doc.text("SURAT PERINGATAN", 105, 20, { align: "center" });
    
    doc.setFontSize(12);
    doc.text(pengaturan.namaInstitusi, 105, 30, { align: "center" });
    doc.text(pengaturan.alamatInstitusi, 105, 35, { align: "center" });
    doc.text(`Telp: ${pengaturan.telpInstitusi} | Email: ${pengaturan.emailInstitusi}`, 105, 40, { align: "center" });
    
    // Garis pemisah
    doc.line(20, 45, 190, 45);
    
    // Informasi surat
    let y = 55;
    doc.text(`Nomor: ${surat.nomorSurat}`, 20, y);
    doc.text(`Tanggal: ${formatTanggalIndonesia(surat.tanggalSurat)}`, 20, y + 5);
    
    y += 15;
    doc.text("Yang bertanda tangan di bawah ini:", 20, y);
    y += 5;
    doc.setFont("helvetica", "bold");
    doc.text(surat.penandatangan, 20, y);
    doc.setFont("helvetica", "normal");
    y += 5;
    doc.text("Dengan ini memberikan Surat Peringatan kepada:", 20, y);
    
    y += 10;
    doc.text(`Nama: ${surat.mahasiswa}`, 20, y);
    y += 5;
    doc.text(`NIM: ${surat.nim}`, 20, y);
    y += 5;
    doc.text(`Program Studi: ${surat.programStudi || '-'}`, 20, y);
    y += 5;
    doc.text(`Semester: ${surat.semester || '-'}`, 20, y);
    
    y += 10;
    doc.setFont("helvetica", "bold");
    doc.text(`Jenis Pelanggaran: ${surat.jenisPelanggaran === 'akademik' ? 'Akademik' : 
        surat.jenisPelanggaran === 'etika' ? 'Etika' : 
        surat.jenisPelanggaran === 'administrasi' ? 'Administrasi' : 'Lainnya'}`, 20, y);
    doc.setFont("helvetica", "normal");
    
    y += 10;
    doc.text("Keterangan:", 20, y);
    y += 5;
    const keteranganLines = doc.splitTextToSize(surat.keterangan, 170);
    doc.text(keteranganLines, 20, y);
    y += keteranganLines.length * 5 + 5;
    
    doc.text("Sanksi:", 20, y);
    y += 5;
    const sanksiLines = doc.splitTextToSize(surat.sanksi, 170);
    doc.text(sanksiLines, 20, y);
    y += sanksiLines.length * 5 + 5;
    
    doc.text(`Surat peringatan ini diberikan atas pelanggaran yang terjadi pada tanggal: ${formatTanggalIndonesia(surat.tanggalPelanggaran)}`, 20, y);
    
    // Tanda tangan
    y += 20;
    doc.text("Hormat kami,", 20, y);
    y += 20;
    doc.text(surat.penandatangan, 20, y);
    
    // Save PDF
    const fileName = `Surat-Peringatan-${surat.nomorSurat.replace(/\//g, '-')}.pdf`;
    doc.save(fileName);
    
    showNotification(`PDF berhasil di-download: ${fileName}`, 'success');
}

function generatePDFWithHTML2PDF() {
    // Fallback method jika jsPDF tidak tersedia
    const surat = currentSuratView;
    const fileName = `Surat-Peringatan-${surat.nomorSurat.replace(/\//g, '-')}.pdf`;
    
    // Buat konten HTML untuk print
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>${surat.nomorSurat}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .surat-container { max-width: 800px; margin: 0 auto; padding: 20px; }
                .surat-header { text-align: center; margin-bottom: 30px; }
                .surat-header h1 { font-size: 24px; margin-bottom: 10px; }
                .surat-header h2 { font-size: 18px; margin-bottom: 5px; }
                .surat-content { margin-bottom: 30px; }
                .surat-field { margin-bottom: 10px; }
                .surat-field label { font-weight: bold; display: inline-block; width: 150px; }
                .surat-paragraph { margin: 15px 0; }
                .surat-footer { margin-top: 50px; }
                .ttd-container { text-align: center; }
                .ttd-space { height: 80px; }
                @media print {
                    body { -webkit-print-color-adjust: exact; }
                }
            </style>
        </head>
        <body>
            ${buatTemplateSurat(surat, {})}
        </body>
        </html>
    `;
    
    // Buka jendela baru untuk print
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
    
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
        showNotification(`PDF siap di-print: ${fileName}`, 'success');
    }, 500);
}