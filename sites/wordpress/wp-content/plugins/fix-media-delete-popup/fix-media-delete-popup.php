<?php
/**
 * Plugin Name: Fix Media Delete Popup
 * Description: Supprime la fausse popup et redirige vers la médiathèque après suppression sur `upload.php?item=ID`.
 * Version: 1.6
 * Author: Graph and Co
 */

add_action('admin_footer-upload.php', function () {
    ?>
    <script>
    (function($) {
      const originalAlert = window.alert;

      window.alert = function(msg) {
        if (
          typeof msg === 'string' &&
          msg.includes('Erreur lors de la suppression du fichier joint')
        ) {
          console.warn('🟡 Fausse popup bloquée');

          setTimeout(() => {
            const currentUrl = new URL(window.location.href);

            // ✅ Si on est sur une page ?item=123, on redirige vers /wp-admin/upload.php
            if (currentUrl.searchParams.get('item')) {
              console.log('↩️ Redirection vers la médiathèque');
              window.location.href = currentUrl.pathname.split('?')[0];
            }
          }, 500);

          return;
        }

        originalAlert(msg);
      };
    })(jQuery);
    </script>
    <?php
});