<?php
/** DecorArt Core — identidade visual do painel administrativo. */
if (!defined('ABSPATH')) { exit; }

function da_admin_brand_asset() { return esc_url(get_template_directory_uri() . '/assets/img/decorart-logo.jpg'); }
function da_admin_site_icon() {
    $logo = add_query_arg('ver', '1.0.1', get_template_directory_uri() . '/assets/img/decorart-logo.jpg');
    echo '<link rel="icon" href="' . $logo . '" type="image/jpeg">';
    echo '<link rel="apple-touch-icon" href="' . $logo . '">';
}
add_action('admin_head', 'da_admin_site_icon', 1);
add_action('login_head', 'da_admin_site_icon', 1);
function da_admin_brand_bar($bar) {
    $bar->remove_node('wp-logo');
    $bar->add_node(['id' => 'da-brand', 'title' => '<img src="'.da_admin_brand_asset().'" alt="DecorArt">', 'href' => admin_url('admin.php?page=decorart-admin'), 'meta' => ['title' => 'Sistema DecorArt', 'class' => 'da-admin-brand-node']]);
}
add_action('admin_bar_menu', 'da_admin_brand_bar', 1);
function da_admin_footer_text() { return '<strong>Sistema DecorArt</strong> · gestão de clientes e eventos'; }
add_filter('admin_footer_text', 'da_admin_footer_text');
function da_admin_footer_version() { return 'DecorArt'; }
add_filter('update_footer', 'da_admin_footer_version', 11);
function da_admin_brand_styles() {
    wp_register_style('decorart-admin-brand', false, [], '1.0.0'); wp_enqueue_style('decorart-admin-brand');
    wp_add_inline_style('decorart-admin-brand', ':root{--da-teal:#247f79;--da-teal-dark:#145d5a;--da-cream:#fff8f0;--da-paper:#fffcf7;--da-coral:#e84d24;--da-yellow:#fcad33;--da-ink:#2f2925}body.wp-admin{background:var(--da-cream)}#wpadminbar{background:var(--da-ink)}#wpadminbar .ab-item,#wpadminbar a.ab-item{color:#fff8f0}#wpadminbar .ab-item:hover,#wpadminbar a.ab-item:hover{background:var(--da-teal-dark);color:#fff}#wpadminbar #wp-admin-bar-da-brand>.ab-item{height:32px;padding:0 10px;display:flex;align-items:center;background:var(--da-teal-dark)}#wpadminbar #wp-admin-bar-da-brand img{display:block;width:72px;height:25px;object-fit:contain;object-position:left center;border-radius:3px}#adminmenuback,#adminmenuwrap,#adminmenu{background:var(--da-ink)}#adminmenu a{color:#fff8f0}#adminmenu div.wp-menu-image:before{color:#eadfd4}#adminmenu a:hover,#adminmenu li.menu-top:hover,#adminmenu li.opensub>a.menu-top{background:var(--da-teal-dark);color:#fff}#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,#adminmenu li.current a.menu-top,#adminmenu li.wp-has-current-submenu{background:var(--da-teal);color:#fff}#adminmenu .wp-submenu{background:#211c18}#adminmenu .wp-submenu a{color:#f5e2c8}#adminmenu .wp-submenu a:hover{color:#fff;background:var(--da-teal-dark)}#adminmenu .awaiting-mod,#adminmenu .update-plugins{background:var(--coral,#e84d24)}#collapse-menu{color:#f5e2c8}#collapse-menu:hover{color:#fff;background:var(--da-teal-dark)}#wpfooter{color:#746b64}#wpfooter a{color:var(--da-teal)}#wpfooter a:hover{color:var(--da-teal-dark)}.update-nag,.notice{border-left-color:var(--da-teal)}.wrap h1,.wrap h2{color:var(--da-ink)}@media(max-width:782px){#wpadminbar #wp-admin-bar-da-brand img{width:66px;height:25px}#adminmenuwrap{top:46px}.da-admin-wrap{margin-top:15px!important}}');
}
add_action('admin_enqueue_scripts', 'da_admin_brand_styles', 20);
function da_login_brand() {
    $url = da_admin_brand_asset();
    echo '<style>:root{--da-teal:#247f79;--da-teal-dark:#145d5a;--da-cream:#fff8f0;--da-ink:#2f2925}body.login{background:var(--da-cream)}.login h1 a{background-image:url("'.esc_url($url).'");background-size:contain;background-position:center;background-repeat:no-repeat;width:220px;height:92px}.login form{border-top:4px solid var(--da-teal);border-radius:12px;box-shadow:0 12px 30px rgba(47,41,37,.12)}.login #wp-submit{background:var(--da-teal);border-color:var(--da-teal-dark);text-shadow:none;box-shadow:none}.login #wp-submit:hover,.login #wp-submit:focus{background:var(--da-teal-dark);border-color:var(--da-teal-dark)}.login a{color:var(--da-teal)}.login a:hover{color:var(--da-teal-dark)}</style>';
}
add_action('login_enqueue_scripts', 'da_login_brand');
function da_login_brand_url() { return home_url('/'); }
add_filter('login_headerurl', 'da_login_brand_url');
function da_login_brand_title() { return 'DecorArt — Sistema administrativo'; }
add_filter('login_headertext', 'da_login_brand_title');
