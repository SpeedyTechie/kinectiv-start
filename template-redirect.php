<?php
/**
 * Template Name: Redirect
 */

$f_type = get_field('redirect_type');
$f_page = get_field('redirect_page');
$f_url = get_field('redirect_url');

$redirect_url = home_url();

if ($f_type == 'internal') {
    $redirect_url = $f_page;
} elseif ($f_type == 'external') {
    $redirect_url = $f_url;
}

wp_redirect($redirect_url, 302); // redirect page to selected page/url
