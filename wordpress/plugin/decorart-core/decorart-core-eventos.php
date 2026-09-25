<?php
/**
 * DecorArt Core — Eventos.
 * O CPT continua sendo a entidade central de eventos; a interface operacional fica no painel DecorArt.
 */
if (!defined('ABSPATH')) { exit; }

function da_register_evento_type() {
    register_post_type('da_evento', [
        'labels' => ['name' => 'Eventos', 'singular_name' => 'Evento', 'add_new_item' => 'Adicionar evento', 'edit_item' => 'Editar evento', 'menu_name' => 'Eventos'],
        'public' => false, 'show_ui' => false, 'show_in_rest' => false, 'supports' => ['title'],
        'capability_type' => 'post', 'map_meta_cap' => true,
    ]);
    $fields = [
        '_da_cliente_id' => ['type' => 'integer', 'sanitize_callback' => 'absint'],
        '_da_evento_data' => ['type' => 'string', 'sanitize_callback' => 'da_sanitize_date'],
        '_da_evento_hora' => ['type' => 'string', 'sanitize_callback' => 'da_sanitize_time'],
        '_da_montagem_data' => ['type' => 'string', 'sanitize_callback' => 'da_sanitize_date'],
        '_da_montagem_hora' => ['type' => 'string', 'sanitize_callback' => 'da_sanitize_time'],
        '_da_evento_local' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
        '_da_evento_tema' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
        '_da_evento_observacoes' => ['type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field'],
        '_da_evento_status' => ['type' => 'string', 'sanitize_callback' => 'da_sanitize_event_status'],
    ];
    foreach ($fields as $key => $field) {
        register_post_meta('da_evento', $key, ['type' => $field['type'], 'single' => true, 'show_in_rest' => false, 'sanitize_callback' => $field['sanitize_callback'], 'auth_callback' => static fn() => current_user_can('edit_posts')]);
    }
}
add_action('init', 'da_register_evento_type');
function da_sanitize_date($value) { $value = sanitize_text_field((string) $value); return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : ''; }
function da_sanitize_time($value) { $value = sanitize_text_field((string) $value); return preg_match('/^\d{2}:\d{2}$/', $value) ? $value : ''; }
function da_event_statuses() { return ['pre_reserva' => 'Pré-reserva', 'confirmado' => 'Confirmado', 'realizado' => 'Realizado', 'cancelado' => 'Cancelado']; }
function da_sanitize_event_status($value) { $value = sanitize_key($value); return array_key_exists($value, da_event_statuses()) ? $value : 'pre_reserva'; }
function da_event_status_label($status) { $all = da_event_statuses(); return $all[$status] ?? 'Pré-reserva'; }
function da_event_status_color($status) { return ['pre_reserva' => '#d9a23c', 'confirmado' => '#2f9e6e', 'realizado' => '#3a7bd5', 'cancelado' => '#a94442'][$status] ?? '#7b8794'; }
