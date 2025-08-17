</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo base_url('assets/js/bootstrap.bundle.min.js'); ?>"></script>
<script>
            document.addEventListener('DOMContentLoaded', function() {
                        const hamburgerBtn = document.getElementById('hamburger-btn');
                        const sidebar = document.getElementById('sidebar');
                        const overlay = document.getElementById('sidebar-overlay');

                        if (hamburgerBtn) {
                                    hamburgerBtn.addEventListener('click', function() {
                                                sidebar.classList.toggle('show');
                                                overlay.classList.toggle('show');
                                    });
                        }

                        if (overlay) {
                                    overlay.addEventListener('click', function() {
                                                sidebar.classList.remove('show');
                                                overlay.classList.remove('show');
                                    });
                        }
            });
</script>
</body>

</html>