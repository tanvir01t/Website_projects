<?php
/**
 * includes/footer.php
 * Closes the app shell markup, embeds flash messages for the
 * toast system, and loads shared + page-specific JS.
 */
$flashes = getFlashes();
?>
    </div><!-- /.app-shell -->

    <?php if (!empty($flashes)): ?>
        <script type="application/json" id="flash-data"><?= json_encode($flashes, JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>

    <script src="<?= BASE_URL ?>/assets/js/toast.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
    <?php if (!empty($extraScript)): ?>
        <script src="<?= e($extraScript) ?>"></script>
    <?php endif; ?>
</body>
</html>
