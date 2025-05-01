<?php include '_footer_body.php' ?>

<script>

    document.documentElement.setAttribute('data-js', '')
    window.addEventListener('load', () => document.documentElement.removeAttribute('data-loading'))

    AOS.init({ });

</script>




<?php if (isset($footer['scripts'])) {foreach ($footer['scripts'] as $script) {echo '<script src="'.$script.'"></script>';}} ?>

<?php if (isset($footer['custom_script'])) echo '<script> '.$footer['custom_script'].' </script>'; ?>

</body>
</html>
