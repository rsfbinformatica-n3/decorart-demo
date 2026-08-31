<?php
/**
 * Plugin Name: DecorArt Core
 * Description: Catálogo administrável de itens para o montador de festas DecorArt.
 * Version: 1.0.0
 * Author: RSFBINFORMATICA
 */
if (!defined('ABSPATH')) { exit; }

function da_sanitize_price($value, $meta_key = '', $object_type = '', $object_subtype = '') {
    return max(0, (float) $value);
}

function da_register_item_type() {
    register_post_type('da_item', [
        'labels' => [
            'name' => 'Itens da festa',
            'singular_name' => 'Item da festa',
            'add_new_item' => 'Adicionar item',
            'edit_item' => 'Editar item',
            'menu_name' => 'Catálogo DecorArt',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-art',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
    ]);
    foreach (['_da_price', '_da_category', '_da_icon'] as $key) {
        register_post_meta('da_item', $key, [
            'type' => $key === '_da_price' ? 'number' : 'string',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => $key === '_da_price' ? 'da_sanitize_price' : 'sanitize_text_field',
            'auth_callback' => static fn() => current_user_can('edit_posts'),
        ]);
    }
}
add_action('init', 'da_register_item_type');

function da_item_meta_box() {
    add_meta_box('da_item_details', 'Detalhes no montador', 'da_item_meta_box_html', 'da_item', 'side', 'high');
}
add_action('add_meta_boxes', 'da_item_meta_box');

function da_item_meta_box_html($post) {
    wp_nonce_field('da_save_item', 'da_item_nonce');
    $price = get_post_meta($post->ID, '_da_price', true);
    $category = get_post_meta($post->ID, '_da_category', true);
    $icon = get_post_meta($post->ID, '_da_icon', true);
    ?>
    <p><label for="da_price"><strong>Valor (R$)</strong></label><br><input id="da_price" name="da_price" type="number" min="0" step="0.01" value="<?php echo esc_attr($price); ?>" style="width:100%"></p>
    <p><label for="da_category"><strong>Categoria</strong></label><br><input id="da_category" name="da_category" type="text" value="<?php echo esc_attr($category); ?>" placeholder="Ex.: Mobiliário" style="width:100%"></p>
    <p><label for="da_icon"><strong>Ícone curto</strong></label><br><input id="da_icon" name="da_icon" type="text" maxlength="4" value="<?php echo esc_attr($icon); ?>" placeholder="✦" style="width:100%"></p>
    <p><small>Use o resumo do item para explicar sua função no catálogo.</small></p>
    <?php
}

function da_save_item_meta($post_id) {
    if (!isset($_POST['da_item_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['da_item_nonce'])), 'da_save_item')) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }
    if (isset($_POST['da_price'])) { update_post_meta($post_id, '_da_price', max(0, (float) $_POST['da_price'])); }
    if (isset($_POST['da_category'])) { update_post_meta($post_id, '_da_category', sanitize_text_field(wp_unslash($_POST['da_category']))); }
    if (isset($_POST['da_icon'])) { update_post_meta($post_id, '_da_icon', sanitize_text_field(wp_unslash($_POST['da_icon']))); }
}
add_action('save_post_da_item', 'da_save_item_meta');

function da_admin_notice() {
    if (!current_user_can('manage_options')) { return; }
    echo '<div class="notice notice-warning"><p><strong>DecorArt:</strong> os itens e valores iniciais são demonstrativos. Substitua-os pelo catálogo oficial antes do uso comercial.</p></div>';
}
add_action('admin_notices', 'da_admin_notice');
