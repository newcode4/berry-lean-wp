<?php
namespace BerryLean;

class Front {
  public static function init(){
    add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_base'], 9);
    add_action('wp_enqueue_scripts', [__CLASS__, 'inject_root_vars'], 20);
    add_action('wp_enqueue_scripts', [__CLASS__, 'apply_fonts'], 24);
    add_action('wp_enqueue_scripts', [__CLASS__, 'apply_containers'], 25);
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

  /** 폰트 유틸 – 항상 생성 */
  public static function apply_fonts(){
    $base = get_option('berry_font_base_size','16px');
    $lh   = get_option('berry_font_base_lh','1.6');
    $trk_kr = get_option('berry_font_track_kr','-0.04em');
    $trk_en = get_option('berry_font_track_en','0em');

    $css = <<<CSS
/* Font utilities */
.font-sans{ font-family: var(--font-sans); }
.font-title{ font-family: var(--font-title); }
.text-body{ font-size: {$base}; line-height: {$lh}; color: var(--fg); letter-spacing: {$trk_kr}; }
.text-muted{ color: var(--muted); }
.tracking-kr{ letter-spacing: {$trk_kr}; }
.tracking-en{ letter-spacing: {$trk_en}; }
.h1{ font-family: var(--font-title); font-size: calc({$base} * 2.0); line-height: 1.25; letter-spacing: {$trk_kr}; }
.h2{ font-family: var(--font-title); font-size: calc({$base} * 1.6); line-height: 1.28; letter-spacing: {$trk_kr}; }
.h3{ font-family: var(--font-title); font-size: calc({$base} * 1.3); line-height: 1.32; letter-spacing: {$trk_kr}; }
.h4{ font-family: var(--font-title); font-size: calc({$base} * 1.15); line-height: 1.35; letter-spacing: {$trk_kr}; }
CSS;
    wp_add_inline_style(BERRY_LEAN_STYLE, $css);
  }

  /** 컨테이너 유틸 – preset 3종 + custom 전부 CSS 생성 */
  public static function apply_containers(){
    $CONF = get_option('berry_containers');
    if (!$CONF) $CONF = \BerryLean\Admin::default_containers();

    $mk = function($class, $cfg){
      $py = $cfg['py']; $px=$cfg['px']; $gap=$cfg['gap'];
      $bt = !empty($cfg['bt']) ? "border-top:1px solid var(--border);" : "";
      $bb = !empty($cfg['bb']) ? "border-bottom:1px solid var(--border);" : "";
      $class = preg_replace('~[^a-zA-Z0-9\-_]~','',$class);
      return <<<CSS
.{$class}{
  max-width: var(--container);
  margin-inline:auto;
  display:flex; align-items:center;
  gap: {$gap['desktop']};
  padding: {$py['desktop']} {$px['desktop']};
  {$bt} {$bb}
}
@media (max-width: 1024px){
  .{$class}{ gap: {$gap['tablet']}; padding: {$py['tablet']} {$px['tablet']}; }
}
@media (max-width: 767px){
  .{$class}{ gap: {$gap['mobile']}; padding: {$py['mobile']} {$px['mobile']}; }
}
CSS;
    };

    $css = '';
    foreach (['header','body','title'] as $k){
      if (!empty($CONF[$k])) $css .= $mk($CONF[$k]['class'], $CONF[$k]);
    }
    if (!empty($CONF['custom']) && is_array($CONF['custom'])){
      foreach ($CONF['custom'] as $c) $css .= $mk($c['class'],$c);
    }
    if ($css) wp_add_inline_style(BERRY_LEAN_STYLE, $css);
  }

  public static function apply_snippets(){
    $selected = (array) get_option('berry_snippets', []);
    if (!$selected) return;
    Snippets::apply_selected($selected);
  }
}
