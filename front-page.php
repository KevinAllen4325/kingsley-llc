<?php
/**
 * Template Name: Front Page
 * Description: A Page Template for the Homepage.
 */

defined('ABSPATH') or die;
use Timber\Timber;    

// We need to render contents of <head> before plugin content gets added.
$context              = Timber::get_context();
$post                 = Timber::query_post();
$context['post']      = $post;
Timber::render(['page-' . $post->post_name . '.html.twig', 'front-page.twig'], $context);