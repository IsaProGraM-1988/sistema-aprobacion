    </div> <!-- Fin container-fluid -->
    
    <?php if (Session::isLoggedIn()): ?>
        </div> <!-- Fin content -->
    </div> <!-- Fin wrapper -->
    <?php endif; ?>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
    <script src="<?php echo BASE_URL; ?>public/js/solicitudes.js"></script>
    <script src="<?php echo BASE_URL; ?>public/js/validaciones.js"></script>
    
    <?php if (isset($scripts)) echo $scripts; ?>
</body>
</html>