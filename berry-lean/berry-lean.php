<?php
/**
 * Plugin Name: 베리워크 린 유틸
 * Version: 1.2.0
 * Description: 초경량 공통 유틸 CSS(enqueue) + 사이트 변수/브랜드 
 * Author: Berrywalk
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * Tested up to: 6.6
 * Text Domain: berry-lean
 */


# ===== 0) GitHub 업데이트 체크 (PUC)
require __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;


$updateChecker = PucFactory::buildUpdateChecker(
  'https://github.com/newcode4/berry-lean-wp', // 네 공개 레포 URL
  __FILE__,
  'berry-lean'                                   // slug
);
$updateChecker->setBranch('main');
$updateChecker->getVcsApi()->enableReleaseAssets();

// ===============================
// 1) 공통 CSS 로드
// ===============================
add_action('wp_enqueue_scripts', function () {
  // 배포 CSS (CDN/Pages 등): 버전 파라미터는 URL에서 직접 관리
  $src = 'https://berry-design-system.pages.dev/berry.css?v=20250826';
  wp_enqueue_style('berry-lean', $src, [], null, 'all');
}, 10);

// ===============================
// 2) 사이트별 변수(:root) 주입
// ===============================
add_action('wp_enqueue_scripts', function () {
  // 관리자에서 저장한 옵션값 로드 (기본값 포함)
  $primary    = get_option('berry_primary',   '#2F855A');
  $primary600 = get_option('berry_primary600','#276749');
  $fg         = get_option('berry_fg',        '#222222');
  $bg         = get_option('berry_bg',        '#FFFFFF');
  $muted      = get_option('berry_muted',     '#6B7280');
  $border     = get_option('berry_border',    '#E5E7EB');
  $container  = get_option('berry_container', '1200px');

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

  wp_add_inline_style('berry-lean', $css);

  // 추가 커스텀 변수(관리자 ‘추가 CSS 변수’ 텍스트에 직접 입력한 것)도 뒤에 붙이기
  $extra = trim((string) get_option('berry_extra_vars', ''));
  if ($extra !== '') {
    // 간단한 안전 처리
    $extra = wp_kses($extra, []);
    wp_add_inline_style('berry-lean', $extra);
  }
}, 20);

// ===============================
// 3) <html data-brand="..."> 주입 (브랜드 스위치가 필요할 때만)
// ===============================
add_filter('language_attributes', function ($output) {
  $brand = trim((string) get_option('berry_brand', ''));
  if ($brand !== '') {
    $output .= ' data-brand="' . esc_attr($brand) . '"';
  }
  return $output;
});

// ===============================
// 4) 관리자 설정 페이지 (Settings API)
// ===============================
add_action('admin_init', function(){

  // ── 옵션 등록(색상은 sanitize_hex_color, 나머지는 텍스트)
  register_setting('berry_lean', 'berry_primary',    ['sanitize_callback'=>'sanitize_hex_color']);
  register_setting('berry_lean', 'berry_primary600', ['sanitize_callback'=>'sanitize_hex_color']);
  register_setting('berry_lean', 'berry_fg',         ['sanitize_callback'=>'sanitize_hex_color']);
  register_setting('berry_lean', 'berry_bg',         ['sanitize_callback'=>'sanitize_hex_color']);
  register_setting('berry_lean', 'berry_muted',      ['sanitize_callback'=>'sanitize_hex_color']);
  register_setting('berry_lean', 'berry_border',     ['sanitize_callback'=>'sanitize_hex_color']);

  register_setting('berry_lean', 'berry_container',  ['sanitize_callback'=>'sanitize_text_field']);
  register_setting('berry_lean', 'berry_font_sans',  ['sanitize_callback'=>'sanitize_text_field']);
  register_setting('berry_lean', 'berry_font_title', ['sanitize_callback'=>'sanitize_text_field']);
  register_setting('berry_lean', 'berry_brand',      ['sanitize_callback'=>'sanitize_text_field']);
  register_setting('berry_lean', 'berry_extra_vars', ['sanitize_callback'=>'wp_kses_post']);

  add_settings_section(
    'berry_lean_section_colors',
    '색상/레이아웃 변수 (:root)',
    function(){ echo '<p>사이트 메인 컬러와 기본 텍스트/배경, 컨테이너 폭을 지정합니다.</p>'; },
    'berry-lean'
  );

  // ── 필드 렌더 헬퍼 (인자 2개만 받도록 정리)
  $color = function($id, $default){
    $v = esc_attr( get_option($id, $default) );
    echo "<input type='text' id='{$id}' name='{$id}' value='{$v}' class='berry-color-field' data-default-color='{$default}' />";
  };
  $text = function($id, $ph='', $style=''){
    $v = esc_attr( get_option($id, '') );
    echo "<input type='text' id='{$id}' name='{$id}' value='{$v}' class='regular-text' placeholder='{$ph}' style='{$style}' />";
  };
  $textarea = function($id, $rows=6, $ph=''){
    $v = esc_textarea( get_option($id, '') );
    echo "<textarea id='{$id}' name='{$id}' rows='{$rows}' class='large-text code' placeholder='{$ph}'>{$v}</textarea>";
  };

  // ── 색상/레이아웃 필드
  add_settings_field('berry_primary',    '메인 컬러 (--primary)',          function() use($color){ $color('berry_primary',    '#2F855A'); }, 'berry-lean','berry_lean_section_colors');
  add_settings_field('berry_primary600', '메인 컬러 호버 (--primary-600)', function() use($color){ $color('berry_primary600', '#276749'); }, 'berry-lean','berry_lean_section_colors');
  add_settings_field('berry_fg',         '본문 글자색 (--fg)',             function() use($color){ $color('berry_fg',         '#222222'); }, 'berry-lean','berry_lean_section_colors');
  add_settings_field('berry_bg',         '배경색 (--bg)',                  function() use($color){ $color('berry_bg',         '#FFFFFF'); }, 'berry-lean','berry_lean_section_colors');
  add_settings_field('berry_muted',      '보조 텍스트 (--muted)',          function() use($color){ $color('berry_muted',      '#6B7280'); }, 'berry-lean','berry_lean_section_colors');
  add_settings_field('berry_border',     '테두리 (--border)',              function() use($color){ $color('berry_border',     '#E5E7EB'); }, 'berry-lean','berry_lean_section_colors');
  add_settings_field('berry_container',  '컨테이너 폭 (--container)',      function() use($text){  $text('berry_container','예: 1200px'); }, 'berry-lean','berry_lean_section_colors');

  // ── 폰트 섹션
  add_settings_section(
    'berry_lean_section_fonts',
    '폰트 패밀리 변수',
    function(){
      echo '<p>CSS 변수로 들어가므로, 정확한 패밀리 문자열을 입력하세요. 예) <code>\'Pretendard\', system-ui, -apple-system, \'Noto Sans KR\', sans-serif</code></p>';
    },
    'berry-lean'
  );
  add_settings_field('berry_font_sans',  '본문 폰트 (--font-sans)',  function() use($text){ $text('berry_font_sans',  "'Pretendard', system-ui, -apple-system, 'Noto Sans KR', sans-serif", 'width:600px;'); }, 'berry-lean','berry_lean_section_fonts');
  add_settings_field('berry_font_title', '타이틀 폰트 (--font-title)', function() use($text){ $text('berry_font_title', "'Paperlogy','Pretendard', system-ui, -apple-system, 'Noto Sans KR', sans-serif", 'width:600px;'); }, 'berry-lean','berry_lean_section_fonts');

  // ── 브랜드/추가 변수
  add_settings_section(
    'berry_lean_section_brand',
    '브랜드/추가 변수',
    function(){ echo '<p><code>&lt;html data-brand="..."&gt;</code> 속성으로 브랜드 스위칭이 필요할 때만 사용.</p>'; },
    'berry-lean'
  );
  add_settings_field('berry_brand',      '브랜드 식별자 (data-brand)', function() use($text){ $text('berry_brand','예: cheongeun'); }, 'berry-lean','berry_lean_section_brand');
  add_settings_field('berry_extra_vars', '추가 CSS 변수 블록',         function() use($textarea){
    $textarea('berry_extra_vars', 6, ":root{\n  /* 예: 프로젝트 전용 토큰 */\n  --cta: #F59E0B;\n}\n");
  }, 'berry-lean','berry_lean_section_brand');

});

add_action('admin_enqueue_scripts', function($hook){
  if ($hook !== 'settings_page_berry-lean') return;
  wp_enqueue_style('wp-color-picker');
  wp_enqueue_script('wp-color-picker');
  wp_enqueue_script('underscore'); // _.throttle 사용

  // 입력 즉시 프리뷰에 반영 (저장 전 시각 확인용)
  $js = <<<JS
  (function($){
    function applyPreview(){
      // color fields 즉시 적용 (미리보기 영역은 이미 CSS 변수 사용 중)
      $('.berry-color-field').each(function(){
        var id = this.id, val = $(this).val();
        if (/^#[0-9A-Fa-f]{3,8}$/.test(val)) {
          // id가 berry_primary -> --primary로 매핑
          var cssVar = '--' + id.replace(/^berry_/, '');
          document.documentElement.style.setProperty(cssVar, val);
        }
      });
    }
    function bind(){
      $('.berry-color-field').wpColorPicker({
        change: _.throttle(function(){ applyPreview(); }, 80),
        clear: function(){ applyPreview(); }
      });
      $(document).on('input', '#berry_container, #berry_font_sans, #berry_font_title', _.throttle(applyPreview, 120));
    }
    $(bind);
  })(jQuery);
  JS;
  wp_add_inline_script('underscore', $js);
});

// ===============================
// 관리자 메뉴: 설정 페이지 등록
// ===============================
add_action('admin_menu', function () {
  add_options_page(
    'Berry Lean',          // 페이지 타이틀
    'Berry Lean',          // 메뉴 이름 (설정 > Berry Lean)
    'manage_options',      // 권한
    'berry-lean',          // slug (admin_enqueue_scripts의 hook 식별자와 연결됨)
    function () {          // 렌더 콜백
      ?>
      <div class="wrap">
        <h1>Berry Lean – 사이트 토큰</h1>
        <p>여기서 바꾼 값은 전역 CSS 변수로 주입되고, 유틸 클래스가 바로 반영됩니다.</p>
        <form method="post" action="options.php">
          <?php
            settings_fields('berry_lean');          // register_setting과 짝
            do_settings_sections('berry-lean');     // add_settings_section/field 출력
            submit_button();
          ?>
        </form>
      </div>
      <?php
    }
  );
});
