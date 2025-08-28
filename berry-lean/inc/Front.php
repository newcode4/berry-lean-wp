<?php
namespace BerryLean;

class Front {
  public static function init(){
    add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_base'], 9);
    add_action('wp_enqueue_scripts', [__CLASS__, 'inject_root_vars'], 20);
    add_filter('language_attributes', [__CLASS__, 'brand_data']);
    add_action('wp_enqueue_scripts', [__CLASS__, 'apply_snippets'], 30);
  }

  public static function enqueue_base(){
    wp_enqueue_style(BERRY_LEAN_STYLE, 'https://berry-design-system.pages.dev/berry.css?v=20250826', [], null, 'all');
    if (!wp_script_is(BERRY_LEAN_RT, 'registered')) {
      wp_register_script(BERRY_LEAN_RT, '', [], null, true);
    }
    wp_enqueue_script(BERRY_LEAN_RT);
  }

  public static function inject_root_vars(){
    $primary    = get_option('berry_primary',    '#2F855A');
    $primary600 = get_option('berry_primary600', '#276749');
    $fg         = get_option('berry_fg',         '#222222');
    $bg         = get_option('berry_bg',         '#FFFFFF');
    $muted      = get_option('berry_muted',      '#6B7280');
    $border     = get_option('berry_border',     '#E5E7EB');
    $container  = get_option('berry_container',  '1200px');
    $font_sans  = get_option('berry_font_sans',  "'Pretendard', system-ui, -apple-system, 'Noto Sans KR', sans-serif");
    $font_title = get_option('berry_font_title', "'Paperlogy','Pretendard', system-ui, -apple-system, 'Noto Sans KR', sans-serif");

    $css = ":root{
      --primary: {$primary};
      --primary-600: {$primary600};
      --fg: {$fg};
      --bg: {$bg};
      --muted: {$muted};
      --border: {$border};
      --container: {$container};
      --font-sans: {$font_sans};
      --font-title: {$font_title};
    }";
    wp_add_inline_style(BERRY_LEAN_STYLE, $css);

    $extra = trim((string) get_option('berry_extra_vars', ''));
    if ($extra !== '') {
      $extra = wp_kses($extra, []);
      wp_add_inline_style(BERRY_LEAN_STYLE, $extra);
    }
  }

  public static function brand_data($output){
    $brand = trim((string) get_option('berry_brand', ''));
    if ($brand !== '') $output .= ' data-brand="'.esc_attr($brand).'"';
    return $output;
  }

  public static function apply_snippets(){
    $selected = (array) get_option('berry_snippets', []);
    if (!$selected) return;
    Snippets::apply_selected($selected);
  }
}
