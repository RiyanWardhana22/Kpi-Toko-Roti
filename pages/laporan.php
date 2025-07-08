<?php
?>

<div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Pusat Laporan & Analisis</h1>
            <p class="mb-4">Pilih jenis laporan yang ingin Anda lihat. Setiap laporan akan membantu Anda menganalisis performa produksi dari berbagai sudut pandang.</p>

            <div class="row">
                        <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                                <div class="card-body">
                                                            <h5 class="card-title">Analisis Produk Gagal (Waste)</h5>
                                                            <p class="card-text">Lihat penyebab utama produk gagal untuk mengidentifikasi masalah dalam proses produksi.</p>
                                                            <a href="<?php echo base_url('index.php?page=laporan_waste'); ?>" class="btn btn-primary">Lihat Laporan</a>
                                                </div>
                                    </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                                <div class="card-body">
                                                            <h5 class="card-title">Laporan Efisiensi Bahan Baku</h5>
                                                            <p class="card-text">Bandingkan penggunaan bahan baku standar dengan penggunaan aktual untuk mengukur efisiensi.</p>
                                                            <a href="<?php echo base_url('index.php?page=laporan_efisiensi'); ?>" class="btn btn-primary">Lihat Laporan</a>
                                                </div>
                                    </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                                <div class="card-body">
                                                            <h5 class="card-title">Laporan Pencapaian Target Produksi</h5>
                                                            <p class="card-text">Visualisasikan perbandingan antara target produksi dengan hasil aktual untuk setiap produk.</p>
                                                            <a href="#" class="btn btn-secondary disabled">Segera Hadir</a>
                                                </div>
                                    </div>
                        </div>
            </div>
</div>