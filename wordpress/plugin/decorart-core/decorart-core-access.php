<?php
/** DecorArt Core — acesso amigável usando autenticação nativa do WordPress. */
if (!defined('ABSPATH')) { exit; }

function da_access_rewrite() {
    add_rewrite_rule('^acesso/?$', 'index.php?da_access=1', 'top');
    if (get_option('da_access_rewrite_version') !== '1') {
        flush_rewrite_rules(false);
        update_option('da_access_rewrite_version', '1');
    }
}
add_action('init', 'da_access_rewrite');
function da_access_query_var($vars) { $vars[] = 'da_access'; return $vars; }
add_filter('query_vars', 'da_access_query_var');

function da_access_redirect_after_login($redirect_to, $requested_redirect_to, $user) {
    if ($user instanceof WP_User && user_can($user, 'edit_posts') && (!$requested_redirect_to || $requested_redirect_to === admin_url() || $requested_redirect_to === admin_url('index.php'))) return admin_url('admin.php?page=decorart-admin');
    return $redirect_to;
}
add_filter('login_redirect', 'da_access_redirect_after_login', 10, 3);

function da_access_template() {
    if (!get_query_var('da_access')) return;
    $error = '';
    if (is_user_logged_in()) {
        if (current_user_can('edit_posts')) { wp_safe_redirect(admin_url('admin.php?page=decorart-admin')); exit; }
        $error = 'Sua conta não tem permissão para acessar a área administrativa DecorArt.';
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['da_access_login'])) {
        if (!isset($_POST['da_access_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['da_access_nonce'])), 'da_access_login')) {
            $error = 'Não foi possível validar esta tentativa. Tente novamente.';
        } else {
            $login = sanitize_user(wp_unslash($_POST['log'] ?? ''));
            $password = (string) wp_unslash($_POST['pwd'] ?? '');
            $user = wp_signon(['user_login' => $login, 'user_password' => $password, 'remember' => true], is_ssl());
            if (is_wp_error($user)) {
                $error = 'Usuário ou senha inválidos.';
            } elseif (!user_can($user, 'edit_posts')) {
                wp_logout();
                $error = 'Sua conta não tem permissão para acessar a área administrativa DecorArt.';
            } else {
                wp_set_current_user($user->ID);
                wp_safe_redirect(admin_url('admin.php?page=decorart-admin')); exit;
            }
        }
    }
    status_header(200); nocache_headers(); get_header();
    ?>
    <main id="conteudo" class="da-access-page">
      <style>.da-access-page{min-height:62vh;padding:70px 20px;background:#fff8f0;display:grid;place-items:center}.da-access-card{width:min(440px,100%);background:#fffcf7;border:1px solid #eadfd4;border-radius:24px;padding:30px;box-shadow:0 18px 55px rgba(88,48,28,.12)}.da-access-card h1{font-family:var(--display);font-size:2.3rem;line-height:1;margin:0 0 10px;color:#2f2925}.da-access-card p{color:#746b64}.da-access-card label{display:block;font-weight:800;margin:17px 0 6px}.da-access-card input{width:100%;padding:12px;border:1px solid #eadfd4;border-radius:10px;background:#fff}.da-access-card button{width:100%;margin-top:22px;border:0;border-radius:999px;padding:14px;background:#247f79;color:#fff;font-weight:900;cursor:pointer}.da-access-card button:hover{background:#145d5a}.da-access-error{background:#f7e7e1;color:#8a3824;border-radius:10px;padding:10px 12px;font-size:.88rem}.da-access-back{display:block;text-align:center;margin-top:16px;color:#247f79;font-weight:800;text-decoration:none}</style>
      <section class="da-access-card" aria-labelledby="da-access-title">
        <span class="eyebrow">Sistema DecorArt</span>
        <h1 id="da-access-title">Acesso administrativo</h1>
        <p>Entre para acessar agenda, clientes e gestão da DecorArt.</p>
        <?php if ($error) : ?><div class="da-access-error" role="alert"><?php echo esc_html($error); ?></div><?php endif; ?>
        <form method="post" action="<?php echo esc_url(home_url('/acesso/')); ?>">
          <?php wp_nonce_field('da_access_login', 'da_access_nonce'); ?>
          <input type="hidden" name="da_access_login" value="1">
          <label for="da-access-log">Usuário</label>
          <input id="da-access-log" name="log" type="text" autocomplete="username" required>
          <label for="da-access-pwd">Senha</label>
          <input id="da-access-pwd" name="pwd" type="password" autocomplete="current-password" required>
          <button type="submit">Entrar no Sistema DecorArt</button>
        </form>
        <a class="da-access-back" href="<?php echo esc_url(home_url('/')); ?>">Voltar ao site</a>
      </section>
    </main>
    <?php get_footer(); exit;
}
add_action('template_redirect', 'da_access_template', 1);
