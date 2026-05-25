</div><!-- /container-fluid -->

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?php if (isset($extra_js)): ?>
    <script src="<?= base_url($extra_js) ?>"></script>
<?php endif; ?>
</body>
</html>