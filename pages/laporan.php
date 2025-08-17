<?php
?>
<div class="container-fluid py-3">
    <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-light-primary p-2 rounded me-3">
                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#673AB7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        </div>
                        <div>
                            <h5 class="card-title mb-1">Penggunaan Bahan Harian</h5>
                            <p class="card-text text-muted small">Lihat rincian bahan baku yang terpakai per hari.</p>
                        </div>
                    </div>
                    <a href="<?php echo base_url('index.php?page=laporan_penggunaan_harian'); ?>" class="btn btn-primary mt-auto">Lihat Laporan</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-light-danger p-2 rounded me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F44336" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" /></svg>
                        </div>
                        <div>
                            <h5 class="card-title mb-1">Analisis Produk Gagal</h5>
                            <p class="card-text text-muted small">Identifikasi penyebab utama produk gagal.</p>
                        </div>
                    </div>
                    <a href="<?php echo base_url('index.php?page=laporan_waste'); ?>" class="btn btn-primary mt-auto">Lihat Laporan</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-light-success p-2 rounded me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                        </div>
                        <div>
                            <h5 class="card-title mb-1">Efisiensi Bahan Baku</h5>
                            <p class="card-text text-muted small">Bandingkan penggunaan standar vs aktual.</p>
                        </div>
                    </div>
                    <a href="<?php echo base_url('index.php?page=laporan_efisiensi'); ?>" class="btn btn-primary mt-auto">Lihat Laporan</a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-light-info p-2 rounded me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /></svg>
                        </div>
                        <div>
                            <h5 class="card-title mb-1">Pencapaian Target</h5>
                            <p class="card-text text-muted small">Visualisasikan perbandingan target vs aktual.</p>
                        </div>
                    </div>
                    <a href="<?php echo base_url('index.php?page=laporan_pencapaian'); ?>" class="btn btn-primary mt-auto">Lihat Laporan</a>
                </div>
            </div>
        </div>
    </div>
</div>