<?php
/**
 * WVMS — Footer
 * Closes layout containers and loads scripts
 */
$currentUser = getCurrentUser();
?>

<?php if ($currentUser): ?>
    </div><!-- /.content-area -->
</div><!-- /.main-wrapper -->
<?php else: ?>
</div><!-- /.public-wrapper -->
<?php endif; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- App Scripts -->
<script src="/assets/js/main.js"></script>
<?php if (isset($loadCharts) && $loadCharts): ?>
<script src="/assets/js/charts.js"></script>
<?php endif; ?>

<?php if (isset($extraScripts)): ?>
    <?php echo $extraScripts; ?>
<?php endif; ?>

</body>
</html>
