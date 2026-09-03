<?php
/**
 * DecorArt Core — Eventos e Vendas
 * Área administrativa: planilha de vendas + calendário de rotina de eventos.
 * Autor: RSFBINFORMATICA
 * Segue o mesmo padrão do decorart-core.php (CPT + meta).
 */
if (!defined('ABSPATH')) { exit; }

/* ============ CPT: Evento (é a venda) ============ */
function da_register_evento_type() {
    register_post_type('da_evento', [
        'labels' => [
            'name'          => 'Eventos',
            'singular_name' => 'Evento',
            'add_new_item'  => 'Adicionar evento/venda',
            'edit_item'     => 'Editar evento',
            'menu_name'     => 'Vendas & Eventos',
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'menu_icon'     => 'dashicons-calendar-alt',
        'menu_position' => 26,
        'supports'      => ['title'],
        'capabilities'  => [
            'create_posts' => 'edit_posts', // limitado a editores+ (ajustável)
        ],
    ]);
    foreach (['_da_evento_data', '_da_cliente', '_da_valor', '_da_status'] as $key) {
        register_post_meta('da_evento', $key, [
            'type'    => in_array($key, ['_da_evento_data'], true) ? 'string' : ($key === '_da_valor' ? 'number' : 'string'),
            'single'  => true,
            'show_in_rest' => true,
            'sanitize_callback' => $key === '_da_valor' ? 'da_sanitize_price' : 'sanitize_text_field',
            'auth_callback' => static fn() => current_user_can('edit_posts'),
        ]);
    }
}
add_action('init', 'da_register_evento_type');

/* ============ Meta box ============ */
function da_evento_meta_box() {
    add_meta_box('da_evento_details', 'Venda / Evento', 'da_evento_meta_box_html', 'da_evento', 'normal', 'high');
}
add_action('add_meta_boxes', 'da_evento_meta_box');

function da_evento_meta_box_html($post) {
    wp_nonce_field('da_save_evento', 'da_evento_nonce');
    $data    = get_post_meta($post->ID, '_da_evento_data', true);
    $cliente = get_post_meta($post->ID, '_da_cliente', true);
    $valor   = get_post_meta($post->ID, '_da_valor', true);
    $status  = get_post_meta($post->ID, '_da_status', true) ?: 'previsto';
    $statuses = ['previsto' => 'Previsto', 'confirmado' => 'Confirmado', 'realizado' => 'Realizado', 'cancelado' => 'Cancelado'];
    ?>
    <p><label for="da_evento_data"><strong>Data do evento</strong></label><br>
       <input id="da_evento_data" name="da_evento_data" type="date" value="<?php echo esc_attr($data); ?>"></p>
    <p><label for="da_cliente"><strong>Cliente</strong></label><br>
       <input id="da_cliente" name="da_cliente" type="text" value="<?php echo esc_attr($cliente); ?>" style="width:100%" placeholder="Nome do cliente"></p>
    <p><label for="da_valor"><strong>Valor (R$)</strong></label><br>
       <input id="da_valor" name="da_valor" type="number" min="0" step="0.01" value="<?php echo esc_attr($valor); ?>" style="width:100%"></p>
    <p><label for="da_status"><strong>Status</strong></label><br>
       <select id="da_status" name="da_status" style="width:100%">
       <?php foreach ($statuses as $k => $l): ?>
           <option value="<?php echo esc_attr($k); ?>" <?php selected($status, $k); ?>><?php echo esc_html($l); ?></option>
       <?php endforeach; ?>
       </select></p>
    <?php
}

function da_save_evento_meta($post_id) {
    if (!isset($_POST['da_evento_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['da_evento_nonce'])), 'da_save_evento')) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }
    if (isset($_POST['da_evento_data']))   { update_post_meta($post_id, '_da_evento_data', sanitize_text_field(wp_unslash($_POST['da_evento_data']))); }
    if (isset($_POST['da_cliente']))        { update_post_meta($post_id, '_da_cliente', sanitize_text_field(wp_unslash($_POST['da_cliente']))); }
    if (isset($_POST['da_valor']))          { update_post_meta($post_id, '_da_valor', max(0, (float) $_POST['da_valor'])); }
    if (isset($_POST['da_status']))         { update_post_meta($post_id, '_da_status', sanitize_key(wp_unslash($_POST['da_status']))); }
}
add_action('save_post_da_evento', 'da_save_evento_meta');

/* ============ Colunas na listagem (planilha) ============ */
function da_evento_set_columns($cols) {
    $n = ['cb' => $cols['cb'], 'title' => 'Evento'];
    $n['da_data'] = 'Data';
    $n['da_cliente'] = 'Cliente';
    $n['da_valor'] = 'Valor';
    $n['da_status'] = 'Status';
    $n['date'] = '';
    return $n;
}
add_action('manage_da_evento_posts_custom_column', 'da_evento_render_column', 10, 2);
function da_evento_render_column($col, $post_id) {
    $valor = get_post_meta($post_id, '_da_valor', true);
    $status = get_post_meta($post_id, '_da_status', true) ?: 'previsto';
    $labels = ['previsto'=>'Previsto','confirmado'=>'Confirmado','realizado'=>'Realizado','cancelado'=>'Cancelado'];
    if ($col === 'da_data')    echo esc_html(get_post_meta($post_id, '_da_evento_data', true));
    if ($col === 'da_cliente') echo esc_html(get_post_meta($post_id, '_da_cliente', true));
    if ($col === 'da_valor')   echo 'R$ ' . number_format((float)$valor, 2, ',', '.');
    if ($col === 'da_status')  echo esc_html($labels[$status] ?? $status);
}

/* ============ Calendário de rotina (submenu) ============ */
function da_evento_calendario_menu() {
    add_submenu_page(
        'edit.php?post_type=da_evento',
        'Calendário de Rotina',
        'Calendário',
        'edit_posts',
        'da-calendario',
        'da_evento_calendario_page'
    );
}
add_action('admin_menu', 'da_evento_calendario_menu');

function da_evento_calendario_page() {
    if (!current_user_can('edit_posts')) { wp_die('Sem permissão.'); }
    $ano = isset($_GET['ano']) ? (int) $_GET['ano'] : (int) date('Y');
    $mes = isset($_GET['mes']) ? (int) $_GET['mes'] : (int) date('m');
    $inicio = "$ano-$mes-01";
    $fim = date('Y-m-t', strtotime($inicio));
    $eventos = get_posts(['post_type'=>'da_evento','numberposts'=>-1,
        'meta_query'=>[['key'=>'_da_evento_data','value'=>[$inicio,$fim],'compare'=>'BETWEEN','type'=>'DATE']]]);
    $porDia = [];
    foreach ($eventos as $e) {
        $d = get_post_meta($e->ID, '_da_evento_data', true);
        $dia = (int) substr($d, 8, 2);
        $porDia[$dia][] = $e;
    }
    $labels = ['previsto'=>'Previsto','confirmado'=>'Confirmado','realizado'=>'Realizado','cancelado'=>'Cancelado'];
    $cores = ['previsto'=>'#d9a23c','confirmado'=>'#2f9e6e','realizado'=>'#3a7bd5','cancelado'=>'#c0392b'];
    $nDias = (int) date('t', strtotime($inicio));
    $prim = (int) date('w', strtotime($inicio)); // 0=dom
    ?>
    <div class="wrap">
      <h1>Calendário de Rotina — <?php echo esc_html(date_i18n('F Y', strtotime($inicio))); ?></h1>
      <p>
        <a class="button" href="?post_type=da_evento&page=da-calendario&ano=<?php echo $mes===1?$ano-1:$ano; ?>&mes=<?php echo $mes===1?12:$mes-1; ?>">‹ Mês anterior</a>
        &nbsp;
        <a class="button" href="?post_type=da_evento&page=da-calendario&ano=<?php echo date('Y'); ?>&mes=<?php echo date('m'); ?>">Hoje</a>
        &nbsp;
        <a class="button" href="?post_type=da_evento&page=da-calendario&ano=<?php echo $mes===12?$ano+1:$ano; ?>&mes=<?php echo $mes===12?1:$mes+1; ?>">Próximo mês ›</a>
      </p>
      <style>
        .da-cal { width:100%; border-collapse:collapse; background:#fff; }
        .da-cal th { background:#c9a646; color:#1a1405; padding:8px; }
        .da-cal td { border:1px solid #e5e5e5; vertical-align:top; height:110px; width:14.28%; padding:4px; }
        .da-cal .dnum { font-weight:700; color:#5a4a20; }
        .da-evt { display:block; font-size:11px; border-radius:4px; padding:2px 4px; margin-top:3px; color:#fff; }
      </style>
      <table class="da-cal">
        <tr><th>Dom</th><th>Seg</th><th>Ter</th><th>Qua</th><th>Qui</th><th>Sex</th><th>Sáb</th></tr>
        <?php
        echo '<tr>';
        for ($c=0; $c<$prim; $c++) { echo '<td></td>'; }
        for ($d=1; $d<=$nDias; $d++) {
            echo '<td><span class="dnum">'.$d.'</span>';
            if (!empty($porDia[$d])) {
                foreach ($porDia[$d] as $e) {
                    $st = get_post_meta($e->ID,'_da_status',true) ?: 'previsto';
                    $cor = $cores[$st] ?? '#c9a646';
                    $edit = get_edit_post_link($e->ID);
                    echo '<a class="da-evt" style="background:'.$cor.'" href="'.esc_url($edit).'">'.esc_html($e->post_title).'</a>';
                }
            }
            echo '</td>';
            if (($prim+$d) % 7 === 0) { echo '</tr><tr>'; }
        }
        echo '</tr>';
        ?>
      </table>
    </div>
    <?php
}