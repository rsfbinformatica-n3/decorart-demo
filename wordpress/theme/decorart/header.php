<?php
if (!defined('ABSPATH')) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>
<header class="site-header" id="topo">
  <div class="shell nav-wrap">
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="DecorArt — início">
      <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/decorart-logo.jpg'); ?>" alt="DecorArt — Tudo para sua festa" width="190" height="134">
    </a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-principal"><span></span><span></span><span></span><span class="sr-only">Abrir menu</span></button>
    <nav id="menu-principal" class="main-nav" aria-label="Navegação principal">
      <a href="#como-funciona">Como funciona</a>
      <a href="#montador">Monte sua festa</a>
      <a href="#experiencia">Experiência</a>
      <a class="nav-cta" href="#montador">Começar agora</a>
    </nav>
  </div>
</header>
