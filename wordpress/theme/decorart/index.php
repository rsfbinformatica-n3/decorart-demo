<?php
get_header();
?>
<main id="conteudo" class="shell simple-page">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <article <?php post_class(); ?>><h1><?php the_title(); ?></h1><?php the_content(); ?></article>
  <?php endwhile; else : ?><h1>Conteúdo em preparação</h1><?php endif; ?>
</main>
<?php get_footer();
