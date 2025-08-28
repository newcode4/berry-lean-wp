<?php
namespace BerryLean;

class SEO {
  public static function init(){
    add_action('wp_head', [__CLASS__, 'output_meta'], 5);
  }
  public static function output_meta(){
    $naver  = trim((string) get_option('berry_naver_verify',''));
    $google = trim((string) get_option('berry_google_verify',''));
    $desc   = trim((string) get_option('berry_meta_description',''));
    $og_t   = trim((string) get_option('berry_og_title',''));
    $og_i   = trim((string) get_option('berry_og_image',''));
    $can    = trim((string) get_option('berry_canonical',''));
    if ($can === '') $can = home_url('/');

    if ($naver)  echo "<meta name='naver-site-verification' content='".esc_attr($naver)."' />\n";
    if ($google) echo "<meta name='google-site-verification' content='".esc_attr($google)."' />\n";
    if ($desc)   echo "<meta name='description' content='".esc_attr($desc)."' />\n";
    if ($og_t)   echo "<meta property='og:title' content='".esc_attr($og_t)."' />\n";
    if ($desc)   echo "<meta property='og:description' content='".esc_attr($desc)."' />\n";
    if ($og_i)   echo "<meta property='og:image' content='".esc_url($og_i)."' />\n";
    if ($can)    echo "<link rel='canonical' href='".esc_url($can)."' />\n";
  }
}
