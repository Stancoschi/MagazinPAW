</main>
        <footer>
            <p>© <?php echo date('Y'); ?> Magazin Virtual. Proiect PAW.</p>
            <?php
            $isAdminPage = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
            $isLoginPage = strpos($_SERVER['PHP_SELF'], '/admin/login.php') !== false;
            if (!$isAdminPage && !$isLoginPage && (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) ) :
            ?>
                <p style="text-align:center; font-size:0.8em;"><a href="<?php echo BASE_URL; ?>/admin/login.php">Autentificare Administrator</a></p>
            <?php endif; ?>
        </footer>
        <script src="<?php echo BASE_URL; ?>/js/script.js"></script>
    </body>
    </html>
    ```

