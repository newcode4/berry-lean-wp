<?php
namespace BerryLean;

class Front {
  public static function init(){
    add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_base'], 9);
    add_action('wp_enqueue_scripts', [__CLASS__, 'inject_root_vars'], 20);
    add_action('wp_enqueue_scripts', [__CLASS__, 'apply_utilities'], 25); // 폰트/컨테이너 유틸
    add_action('wp_enqueue_scripts', [__CLASS__, 'apply_snippets'], 30);
  }

  public static function enqueue_base(){
    wp_enqueue_style(BERRY_LEAN_STYLE, 'https://berry-design-system.pages.dev/berry.css?v=20250826', [], null, 'all');
    if (!wp_script_is(BERRY_LEAN_RT, 'registered')) wp_register_script(BERRY_LEAN_RT,'',[],null,true);
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

    $extra = trim((string) get_option('berry_extra_vars',''));
    if ($extra!==''){ $extra = wp_kses($extra,[]); wp_add_inline_style(BERRY_LEAN_STYLE,$extra); }
  }

  /** 폰트/컨테이너 유틸 클래스 CSS 생성 */
  public static function apply_utilities(){
    // 폰트 유틸
    if ((int) get_option('berry_util_fonts_enable',0) === 1){
      $base = get_option('berry_font_base_size','16px');
      $lh   = get_option('berry_font_base_lh','1.6');

      $css = <<<CSS
/* Font utilities */
.font-sans{ font-family: var(--font-sans); }
.font-title{ font-family: var(--font-title); }
.text-body{ font-size: {$base}; line-height: {$lh}; color: var(--fg); }
.text-muted{ color: var(--muted); }
.h1{ font-family: var(--font-title); font-size: calc({$base} * 2.0); line-height: 1.25; }
.h2{ font-family: var(--font-title); font-size: calc({$base} * 1.6); line-height: 1.28; }
.h3{ font-family: var(--font-title); font-size: calc({$base} * 1.3); line-height: 1.32; }
.h4{ font-family: var(--font-title); font-size: calc({$base} * 1.15); line-height: 1.35; }
CSS;
      wp_add_inline_style(BERRY_LEAN_STYLE, $css);
    }

    // 컨테이너 유틸
    if ((int) get_option('berry_util_container_enable',0) === 1){
      $px = function($id,$def){ $v = trim((string) get_option($id,$def)); return $v===''?$def:$v; };
      $py_d = $px('berry_hdr_pad_y_desktop','40px'); $px_d = $px('berry_hdr_pad_x_desktop','16px'); $gap_d=$px('berry_hdr_gap_desktop','24px');
      $py_t = $px('berry_hdr_pad_y_tablet','32px');  $px_t = $px('berry_hdr_pad_x_tablet','16px'); $gap_t=$px('berry_hdr_gap_tablet','20px');
      $py_m = $px('berry_hdr_pad_y_mobile','24px');  $px_m = $px('berry_hdr_pad_x_mobile','12px'); $gap_m=$px('berry_hdr_gap_mobile','16px');
      $bdt  = (int) get_option('berry_hdr_border_top',0) === 1 ? "border-top:1px solid var(--border);" : "";
      $bdb  = (int) get_option('berry_hdr_border_bottom',0) === 1 ? "border-bottom:1px solid var(--border);" : "";

      $css = <<<CSS
/* Header container utility */
.header_container{
  max-width: var(--container);
  margin-inline:auto;
  display:flex; align-items:center; gap: {$gap_d};
  padding: {$py_d} {$px_d};
  {$bdt} {$bdb}
}
@media (max-width: 1024px){
  .header_container{ gap: {$gap_t}; padding: {$py_t} {$px_t}; }
}
@media (max-width: 767px){
  .header_container{ gap: {$gap_m}; padding: {$py_m} {$px_m}; }
}
CSS;
      wp_add_inline_style(BERRY_LEAN_STYLE, $css);
    }
  }

  public static function apply_snippets(){
    $selected = (array) get_option('berry_snippets', []);
    if (!$selected) return;
    Snippets::apply_selected($selected);
  }
}
