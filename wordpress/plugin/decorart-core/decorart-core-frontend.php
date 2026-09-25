<?php
/** DecorArt Core — painel operacional front-end. */
if (!defined('ABSPATH')) { exit; }

function da_frontend_rewrite() {
    add_rewrite_rule('^sistema/?$', 'index.php?da_system=dashboard', 'top');
    add_rewrite_rule('^sistema/(clientes|agenda|eventos)/?$', 'index.php?da_system=$matches[1]', 'top');
    if (get_option('da_frontend_rewrite_version') !== '2') {
        flush_rewrite_rules(false);
        update_option('da_frontend_rewrite_version', '2');
    }
}
add_action('init', 'da_frontend_rewrite', 2);

function da_frontend_query_vars($vars) { $vars[] = 'da_system'; return $vars; }
add_filter('query_vars', 'da_frontend_query_vars');
function da_frontend_url($route = 'dashboard', $args = []) {
    $path = $route === 'dashboard' ? '/sistema/' : '/sistema/' . trim($route, '/') . '/';
    return add_query_arg($args, home_url($path));
}
function da_frontend_is_route() { return (bool) get_query_var('da_system'); }

function da_frontend_assets() {
    if (!da_frontend_is_route()) return;
    wp_dequeue_script('decorart-builder');
    wp_dequeue_style('admin-bar');
    remove_action('wp_body_open', 'wp_admin_bar_render', 0);
    remove_action('wp_footer', 'wp_admin_bar_render', 1000);
    wp_enqueue_style('decorart-admin-frontend', plugins_url('assets/css/decorart-admin-frontend.css', __FILE__), [], '1.0.0');
    wp_enqueue_script('decorart-admin-frontend', plugins_url('assets/js/decorart-admin-frontend.js', __FILE__), [], '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'da_frontend_assets', 99);

function da_frontend_auth() {
    if (!da_frontend_is_route()) return;
    if (!da_can_system()) { wp_safe_redirect(home_url('/acesso/')); exit; }
    nocache_headers();
}
add_action('template_redirect', 'da_frontend_auth', 1);

function da_frontend_layout($title, $content) {
    $logo = get_template_directory_uri() . '/assets/img/decorart-logo.jpg';
    $logout = wp_logout_url(home_url('/'));
    ?><!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?php echo esc_html($title); ?> · DecorArt</title><?php wp_head(); ?></head><body <?php body_class('da-system-body'); ?>><div class="da-system-app"><header class="da-system-topbar"><a class="da-system-brand" href="<?php echo esc_url(da_frontend_url()); ?>"><img src="<?php echo esc_url($logo); ?>" alt="DecorArt"><span>Sistema DecorArt</span></a><button class="da-system-menu-toggle" type="button" aria-expanded="false" aria-controls="da-system-nav">Menu</button><nav id="da-system-nav" class="da-system-nav" aria-label="Navegação do sistema"><a class="<?php echo get_query_var('da_system') === 'dashboard' ? 'is-active' : ''; ?>" href="<?php echo esc_url(da_frontend_url()); ?>">Dashboard</a><a class="<?php echo get_query_var('da_system') === 'clientes' ? 'is-active' : ''; ?>" href="<?php echo esc_url(da_frontend_url('clientes')); ?>">Clientes</a><a class="<?php echo get_query_var('da_system') === 'agenda' ? 'is-active' : ''; ?>" href="<?php echo esc_url(da_frontend_url('agenda')); ?>">Agenda</a><a class="<?php echo get_query_var('da_system') === 'eventos' ? 'is-active' : ''; ?>" href="<?php echo esc_url(da_frontend_url('eventos')); ?>">Eventos</a><a class="da-system-logout" href="<?php echo esc_url($logout); ?>">Sair</a></nav></header><main class="da-system-main"><div class="da-system-heading"><div><span class="da-system-kicker">Sistema DecorArt</span><h1><?php echo esc_html($title); ?></h1></div></div><?php echo $content; ?></main><footer class="da-system-footer">DecorArt · operação de clientes e eventos</footer></div><?php wp_footer(); ?></body></html><?php exit;
}

function da_frontend_notice() {
    if (isset($_GET['salvo'])) echo '<div class="da-system-alert da-system-alert-success">Dados salvos com sucesso.</div>';
}
function da_frontend_denied($message = 'Você não tem permissão para este módulo.') { return '<div class="da-system-empty"><strong>Acesso restrito</strong><p>'.esc_html($message).'</p></div>'; }
function da_frontend_render() {
    $route = sanitize_key(get_query_var('da_system'));
    ob_start();
    if ($route === 'clientes') da_frontend_clients();
    elseif ($route === 'agenda' || $route === 'eventos') da_frontend_agenda();
    else da_frontend_dashboard();
    $content = ob_get_clean();
    da_frontend_layout($route === 'clientes' ? 'Clientes' : ($route === 'agenda' ? 'Agenda / Planner' : ($route === 'eventos' ? 'Eventos' : 'Dashboard')), $content);
}
add_action('template_redirect', 'da_frontend_render', 5);

function da_frontend_dashboard() {
    $today = current_time('Y-m-d'); $upcoming = da_eventos_between($today, date('Y-m-d', strtotime('+30 days', current_time('timestamp')))); $today_events = da_eventos_between($today, $today); $clientes = wp_count_posts('da_cliente'); $eventos = wp_count_posts('da_evento');
    $pre = count(array_filter($upcoming, static fn($e) => get_post_meta($e->ID, '_da_evento_status', true) === 'pre_reserva'));
    $confirmed = count(array_filter($upcoming, static fn($e) => get_post_meta($e->ID, '_da_evento_status', true) === 'confirmado'));
    echo '<section class="da-kpi-grid"><article><strong>'.(int)($clientes->publish ?? 0).'</strong><span>Clientes cadastrados</span></article><article><strong>'.count($today_events).'</strong><span>Eventos hoje</span></article><article><strong>'.$pre.'</strong><span>Pré-reservas próximas</span></article><article><strong>'.$confirmed.'</strong><span>Confirmados próximos</span></article></section>';
    echo '<div class="da-system-columns"><section class="da-system-card"><div class="da-card-title"><h2>Eventos de hoje</h2><a href="'.esc_url(da_frontend_url('agenda')).'">Abrir agenda</a></div>';
    if (!$today_events) echo '<p class="da-muted">Nenhum evento para hoje.</p>'; else { echo '<div class="da-event-list">'; foreach ($today_events as $event) da_frontend_event_row($event); echo '</div>'; }
    echo '</section><section class="da-system-card"><div class="da-card-title"><h2>Próximos eventos</h2><a href="'.esc_url(da_frontend_url('agenda')).'">Ver todos</a></div>';
    if (!$upcoming) echo '<p class="da-muted">Nenhum evento nos próximos 30 dias.</p>'; else { echo '<div class="da-event-list">'; foreach (array_slice($upcoming, 0, 8) as $event) da_frontend_event_row($event); echo '</div>'; }
    echo '</section></div><section class="da-quick-actions"><a href="'.esc_url(da_frontend_url('clientes', ['acao'=>'novo'])).'">+ Cadastrar cliente</a><a href="'.esc_url(da_frontend_url('eventos', ['acao'=>'novo'])).'">+ Criar evento</a></section>';
}
function da_frontend_event_row($event) { $status = get_post_meta($event->ID, '_da_evento_status', true) ?: 'pre_reserva'; $date = get_post_meta($event->ID, '_da_evento_data', true); echo '<a class="da-event-row" href="'.esc_url(da_frontend_url('eventos', ['acao'=>'editar','id'=>$event->ID])).'"><span class="da-event-date">'.esc_html($date).'</span><span><strong>'.esc_html($event->post_title).'</strong><small>'.esc_html(da_event_status_label($status)).'</small></span></a>'; }

function da_frontend_clients() {
    if (!da_can_clients()) { echo da_frontend_denied('Sua conta não possui a permissão de clientes.'); return; }
    da_frontend_notice(); $action = sanitize_key($_GET['acao'] ?? 'lista'); $id = absint($_GET['id'] ?? 0);
    if (in_array($action, ['novo', 'editar'], true)) { da_frontend_client_form($action === 'editar' ? $id : 0); return; }
    $search = sanitize_text_field(wp_unslash($_GET['busca'] ?? '')); $query = new WP_Query(['post_type'=>'da_cliente','post_status'=>'publish','posts_per_page'=>50,'s'=>$search,'orderby'=>'title','order'=>'ASC']);
    echo '<div class="da-toolbar"><div><p class="da-muted">Cadastro simples e histórico de eventos.</p></div><a class="da-primary-button" href="'.esc_url(da_frontend_url('clientes',['acao'=>'novo'])).'">+ Novo cliente</a></div><section class="da-system-card"><form class="da-search" method="get" action="'.esc_url(da_frontend_url('clientes')).'"><label for="da-busca">Buscar cliente</label><div><input id="da-busca" name="busca" value="'.esc_attr($search).'" placeholder="Nome do cliente"><button type="submit">Buscar</button></div></form><div class="da-client-list">';
    if (!$query->have_posts()) echo '<p class="da-muted">Nenhum cliente cadastrado.</p>'; else while ($query->have_posts()) { $query->the_post(); $cid=get_the_ID(); $active=(bool)get_post_meta($cid,'_da_cliente_ativo',true); echo '<article class="da-client-row"><div><h2>'.esc_html(get_the_title()).'</h2><p>'.esc_html(get_post_meta($cid,'_da_cliente_telefone',true)).' '.($active?'<span class="da-pill da-pill-green">Ativo</span>':'<span class="da-pill">Inativo</span>').'</p></div><a href="'.esc_url(da_frontend_url('clientes',['acao'=>'editar','id'=>$cid])).'">Abrir cliente</a></article>'; } wp_reset_postdata(); echo '</div></section>';
}
function da_frontend_client_form($id = 0) {
    $post = $id ? get_post($id) : null; if ($id && (!$post || $post->post_type !== 'da_cliente')) { echo da_frontend_denied('Cliente não encontrado.'); return; }
    $get = static fn($key) => $id ? get_post_meta($id, $key, true) : ''; echo '<div class="da-toolbar"><p class="da-muted">'.($id?'Atualize os dados e consulte o histórico.':'Cadastre apenas as informações necessárias.').'</p><a class="da-secondary-button" href="'.esc_url(da_frontend_url('clientes')).'">Voltar</a></div><form class="da-system-card da-form" method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="da_save_cliente"><input type="hidden" name="da_frontend" value="1"><input type="hidden" name="cliente_id" value="'.esc_attr($id).'">'.wp_nonce_field('da_save_cliente','da_cliente_nonce',true,false).'<div class="da-form-grid"><label class="da-field da-field-full">Nome<input name="nome" required value="'.esc_attr($post ? $post->post_title : '').'"></label><label class="da-field">Telefone / WhatsApp<input name="telefone" inputmode="tel" value="'.esc_attr($get('_da_cliente_telefone')).'"></label><label class="da-field">E-mail (opcional)<input type="email" name="email" value="'.esc_attr($get('_da_cliente_email')).'"></label><label class="da-field da-field-full">Observações<textarea name="observacoes">'.esc_textarea($get('_da_cliente_observacoes')).'</textarea></label><label class="da-check"><input type="checkbox" name="ativo" value="1" '.checked(!$id || $get('_da_cliente_ativo'),true,false).'> Cliente ativo</label></div><button class="da-primary-button" type="submit">Salvar cliente</button></form>';
    if ($id) { $events=da_cliente_events($id); echo '<section class="da-system-card da-history"><div class="da-card-title"><h2>Histórico de eventos</h2><a class="da-primary-button da-small-button" href="'.esc_url(da_frontend_url('eventos',['acao'=>'novo','cliente_id'=>$id])).'">+ Novo evento para este cliente</a></div>'; if (!$events) echo '<p class="da-muted">Nenhum evento cadastrado.</p>'; else { echo '<div class="da-history-list">'; foreach($events as $event){$st=get_post_meta($event->ID,'_da_evento_status',true)?:'pre_reserva'; echo '<a href="'.esc_url(da_frontend_url('eventos',['acao'=>'editar','id'=>$event->ID])).'"><span>'.esc_html(get_post_meta($event->ID,'_da_evento_data',true)).'</span><strong>'.esc_html($event->post_title).'</strong><em style="--pill-color:'.esc_attr(da_event_status_color($st)).'">'.esc_html(da_event_status_label($st)).'</em></a>'; } echo '</div>'; } echo '</section>'; }
}

function da_frontend_agenda() {
    if (!da_can_events()) { echo da_frontend_denied('Sua conta não possui a permissão de agenda e eventos.'); return; }
    $action=sanitize_key($_GET['acao']??'lista'); $id=absint($_GET['id']??0); da_frontend_notice(); if(in_array($action,['novo','editar'],true)){da_frontend_event_form($action==='editar'?$id:0);return;}
    $view=sanitize_key($_GET['visao']??'mes'); $year=max(2020,min(2100,absint($_GET['ano']??current_time('Y')))); $month=max(1,min(12,absint($_GET['mes']??current_time('m'))));
    echo '<div class="da-toolbar"><div class="da-view-switch"><a class="'.($view==='mes'?'is-active':'').'" href="'.esc_url(da_frontend_url('agenda',['visao'=>'mes','ano'=>$year,'mes'=>$month])).'">Mês</a><a class="'.($view==='semana'?'is-active':'').'" href="'.esc_url(da_frontend_url('agenda',['visao'=>'semana','ano'=>$year,'mes'=>$month])).'">Semana</a></div><a class="da-primary-button" href="'.esc_url(da_frontend_url('eventos',['acao'=>'novo'])).'">+ Novo evento</a></div>';
    if($view==='semana') da_frontend_week($year,$month); else da_frontend_month($year,$month);
}
function da_frontend_month($year,$month){$start=sprintf('%04d-%02d-01',$year,$month);$events=da_eventos_between($start,date('Y-m-t',strtotime($start)));$days=[];foreach($events as $e)$days[(int)date('j',strtotime(get_post_meta($e->ID,'_da_evento_data',true)))][]=$e;$prev=$month===1?['ano'=>$year-1,'mes'=>12]:['ano'=>$year,'mes'=>$month-1];$next=$month===12?['ano'=>$year+1,'mes'=>1]:['ano'=>$year,'mes'=>$month+1];echo '<section class="da-system-card"><div class="da-calendar-head"><a href="'.esc_url(da_frontend_url('agenda',array_merge(['visao'=>'mes'],$prev))).'">‹</a><h2>'.esc_html(date_i18n('F Y',strtotime($start))).'</h2><a href="'.esc_url(da_frontend_url('agenda',array_merge(['visao'=>'mes'],$next))).'">›</a></div><div class="da-calendar-weekdays"><span>Seg</span><span>Ter</span><span>Qua</span><span>Qui</span><span>Sex</span><span>Sáb</span><span>Dom</span></div><div class="da-calendar-grid">';$offset=(int)date('N',strtotime($start))-1;for($i=0;$i<$offset;$i++)echo '<span class="da-calendar-empty"></span>';for($d=1;$d<=date('t',strtotime($start));$d++){$date=sprintf('%04d-%02d-%02d',$year,$month,$d);echo '<a class="da-calendar-day '.($date===current_time('Y-m-d')?'is-today':'').'" href="'.esc_url(da_frontend_url('eventos',['acao'=>'novo','data'=>$date])).'"><b>'.$d.'</b>';foreach(($days[$d]??[]) as $e){$st=get_post_meta($e->ID,'_da_evento_status',true)?:'pre_reserva';echo '<span style="--event-color:'.esc_attr(da_event_status_color($st)).'">'.esc_html($e->post_title).'</span>';}echo '</a>';}echo '</div><p class="da-muted">Toque em uma data para criar um evento.</p></section>';}
function da_frontend_week($year,$month){$base=strtotime(sprintf('%04d-%02d-15',$year,$month));$monday=strtotime('monday this week',$base);$events=da_eventos_between(date('Y-m-d',$monday),date('Y-m-d',strtotime('+6 days',$monday)));$days=[];foreach($events as $e)$days[get_post_meta($e->ID,'_da_evento_data',true)][]=$e;echo '<section class="da-week-grid">';for($i=0;$i<7;$i++){$ts=strtotime('+'.$i.' days',$monday);$date=date('Y-m-d',$ts);echo '<article class="da-week-day"><h2>'.esc_html(date_i18n('D d/m',$ts)).'</h2>';if(empty($days[$date]))echo '<a class="da-empty-day" href="'.esc_url(da_frontend_url('eventos',['acao'=>'novo','data'=>$date])).'">+ Criar evento</a>';foreach(($days[$date]??[]) as $e){$st=get_post_meta($e->ID,'_da_evento_status',true)?:'pre_reserva';echo '<a class="da-week-event" style="--event-color:'.esc_attr(da_event_status_color($st)).'" href="'.esc_url(da_frontend_url('eventos',['acao'=>'editar','id'=>$e->ID])).'"><strong>'.esc_html($e->post_title).'</strong><small>'.esc_html(da_event_status_label($st)).'</small></a>';}echo '</article>';}echo '</section>';}
function da_frontend_event_form($id=0){$post=$id?get_post($id):null;if($id&&(!$post||$post->post_type!=='da_evento')){echo da_frontend_denied('Evento não encontrado.');return;}$saved=static fn($key)=>$id?get_post_meta($id,$key,true):'';$client=(int)($id?get_post_meta($id,'_da_cliente_id',true):absint($_GET['cliente_id']??0));$date=$id?$saved('_da_evento_data'):sanitize_text_field(wp_unslash($_GET['data']??''));$clients=get_posts(['post_type'=>'da_cliente','post_status'=>'publish','numberposts'=>-1,'orderby'=>'title','order'=>'ASC']);echo '<div class="da-toolbar"><p class="da-muted">'.($id?'Atualize os dados do evento.':'Associe o evento a um cliente e preencha os detalhes.').'</p><a class="da-secondary-button" href="'.esc_url(da_frontend_url('agenda')).'">Voltar para agenda</a></div><form class="da-system-card da-form" method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="da_save_evento"><input type="hidden" name="da_frontend" value="1"><input type="hidden" name="evento_id" value="'.esc_attr($id).'">'.wp_nonce_field('da_save_evento','da_evento_nonce',true,false).'<div class="da-form-grid"><label class="da-field da-field-full">Nome do evento<input name="titulo" value="'.esc_attr($post?$post->post_title:'').'" placeholder="Ex.: Aniversário da Maria"></label><label class="da-field">Cliente<select name="cliente_id" required><option value="">Selecione o cliente</option>';foreach($clients as $c)echo '<option value="'.esc_attr($c->ID).'" '.selected($client,$c->ID,false).'>'.esc_html($c->post_title).'</option>';echo '</select></label><label class="da-field">Status<select name="status">';foreach(da_event_statuses() as $key=>$label)echo '<option value="'.esc_attr($key).'" '.selected($saved('_da_evento_status')?:'pre_reserva',$key,false).'>'.esc_html($label).'</option>';echo '</select></label><label class="da-field">Data do evento<input type="date" name="data" required value="'.esc_attr($date).'" ></label><label class="da-field">Horário do evento<input type="time" name="hora" value="'.esc_attr($saved('_da_evento_hora')).'"></label><label class="da-field">Data de montagem<input type="date" name="montagem_data" value="'.esc_attr($saved('_da_montagem_data')).'"></label><label class="da-field">Horário de montagem<input type="time" name="montagem_hora" value="'.esc_attr($saved('_da_montagem_hora')).'"></label><label class="da-field">Local / endereço<input name="local" value="'.esc_attr($saved('_da_evento_local')).'"></label><label class="da-field">Tema<input name="tema" value="'.esc_attr($saved('_da_evento_tema')).'"></label><label class="da-field da-field-full">Observações<textarea name="observacoes">'.esc_textarea($saved('_da_evento_observacoes')).'</textarea></label></div><button class="da-primary-button" type="submit">Salvar evento</button></form>';}
