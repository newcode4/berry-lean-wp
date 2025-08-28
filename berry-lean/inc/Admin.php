<?php
namespace BerryLean;

class Admin {
  public static function init(){
    add_action('admin_init', [__CLASS__, 'register_settings']);
    add_action('admin_menu', [__CLASS__, 'add_menu']);
    add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin']);
  }

  public static function add_menu(){
    add_options_page('Berry Lean', 'Berry Lean', 'manage_options', BERRY_LEAN_SLUG, [__CLASS__, 'render_page']);
  }

  public static function enqueue_admin($hook){
    if ($hook !== 'settings_page_'.BERRY_LEAN_SLUG) return;
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_script('underscore');

    wp_enqueue_style('berry-lean-admin', BERRY_LEAN_URL.'assets/admin.css', [], '1.0.0');
    wp_enqueue_script('berry-lean-admin', BERRY_LEAN_URL.'assets/admin.js', ['jquery','underscore'], '1.0.0', true);
  }

  /** Settings API 등록 (옵션/섹션/필드) */
  public static function register_settings(){
    // ── 공통 옵션 등록
    register_setting(BERRY_LEAN_OPT, 'berry_primary',    ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_primary600', ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_fg',         ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_bg',         ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_muted',      ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_border',     ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_container',  ['sanitize_callback'=>'sanitize_text_field']);

    register_setting(BERRY_LEAN_OPT, 'berry_font_sans',  ['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_font_title', ['sanitize_callback'=>'sanitize_text_field']);

    register_setting(BERRY_LEAN_OPT, 'berry_brand',      ['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_extra_vars', ['sanitize_callback'=>'wp_kses_post']);

    register_setting(BERRY_LEAN_OPT, 'berry_snippets', [
      'sanitize_callback'=>function($arr){
        if (!is_array($arr)) return [];
        $out = array_map('sanitize_text_field', $arr);
        return array_values(array_unique($out));
      }
    ]);
    // 스니펫 변수
    register_setting(BERRY_LEAN_OPT, 'berry_snip_hover_icon_default', ['sanitize_callback'=>'esc_url_raw']);
    register_setting(BERRY_LEAN_OPT, 'berry_snip_hover_icon_hover',   ['sanitize_callback'=>'esc_url_raw']);
    register_setting(BERRY_LEAN_OPT, 'berry_snip_btn_active_bg',      ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_snip_btn_active_fg',      ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_snip_metform_notice',     ['sanitize_callback'=>'sanitize_text_field']);

    // SEO
    register_setting(BERRY_LEAN_OPT, 'berry_naver_verify',    ['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_google_verify',   ['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_meta_description',['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_og_title',        ['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_og_image',        ['sanitize_callback'=>'esc_url_raw']);
    register_setting(BERRY_LEAN_OPT, 'berry_canonical',       ['sanitize_callback'=>'esc_url_raw']);

    // 섹션(탭 역할): 내용은 렌더에서
    foreach (['tab_colors','tab_fonts','tab_brand','tab_snippets','tab_seo'] as $sec){
      add_settings_section($sec, '', function(){}, BERRY_LEAN_SLUG);
    }

    // ── 필드
    self::fields_colors();
    self::fields_fonts();
    self::fields_brand();
    self::fields_snippets();
    self::fields_seo();
  }

  /** Helpers */
  private static function color($id, $default){
    $v = esc_attr(get_option($id,$default));
    echo "<input type='text' id='{$id}' name='{$id}' value='{$v}' class='berry-color-field' data-default-color='{$default}' />";
  }
  private static function text($id, $ph='', $style='width:600px;'){
    $v = esc_attr(get_option($id,''));
    echo "<input type='text' id='{$id}' name='{$id}' value='{$v}' class='regular-text' placeholder='{$ph}' style='{$style}' />";
  }
  private static function textarea($id, $rows=6, $ph=''){
    $v = esc_textarea(get_option($id,''));
    echo "<textarea id='{$id}' name='{$id}' rows='{$rows}' class='large-text code' placeholder='{$ph}'>{$v}</textarea>";
  }

  private static function fields_colors(){
    add_settings_field('berry_primary','메인 (--primary)',          fn()=>self::color('berry_primary','#2F855A'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_primary600','호버(--primary-600)',    fn()=>self::color('berry_primary600','#276749'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_fg','본문(--fg)',                     fn()=>self::color('berry_fg','#222222'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_bg','배경(--bg)',                     fn()=>self::color('berry_bg','#FFFFFF'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_muted','보조(--muted)',               fn()=>self::color('berry_muted','#6B7280'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_border','테두리(--border)',           fn()=>self::color('berry_border','#E5E7EB'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_container','컨테이너(--container)',   fn()=>self::text('berry_container','예: 1200px','width:240px;'), BERRY_LEAN_SLUG,'tab_colors');
  }

  private static function fields_fonts(){
    add_settings_field('berry_font_sans','본문 폰트(--font-sans)',   fn()=>self::text('berry_font_sans',"'Pretendard', system-ui, -apple-system, 'Noto Sans KR', sans-serif"), BERRY_LEAN_SLUG,'tab_fonts');
    add_settings_field('berry_font_title','타이틀 폰트(--font-title)',fn()=>self::text('berry_font_title',"'Paperlogy','Pretendard', system-ui, -apple-system, 'Noto Sans KR', sans-serif"), BERRY_LEAN_SLUG,'tab_fonts');
  }

  private static function fields_brand(){
    add_settings_field('brand_help','<em>안내</em>', fn()=>print('<p><code>&lt;html data-brand="..."&gt;</code> 스위칭용.</p>'), BERRY_LEAN_SLUG,'tab_brand');
    add_settings_field('berry_brand','브랜드 id(data-brand)', fn()=>self::text('berry_brand','예: cheongeun','width:240px;'), BERRY_LEAN_SLUG,'tab_brand');
    add_settings_field('berry_extra_vars','추가 CSS 변수 블록', fn()=>self::textarea('berry_extra_vars',6,":root{\n  --cta:#F59E0B;\n}\n"), BERRY_LEAN_SLUG,'tab_brand');
  }

  private static function fields_snippets(){
    $reg = Snippets::registry();
    add_settings_field('berry_snippets','스니펫 선택', function() use($reg){
      $checked = (array) get_option('berry_snippets',[]);
      echo '<p>체크로 켜고 끕니다.</p>';
      foreach($reg as $id=>$meta){
        $is = in_array($id,$checked,true) ? 'checked' : '';
        printf("<label class='bl-row'><input type='checkbox' name='berry_snippets[]' value='%s' %s/> %s</label>",
          esc_attr($id), $is, esc_html($meta['label'])
        );
      }
      echo '<hr/>';
    }, BERRY_LEAN_SLUG,'tab_snippets');

    add_settings_field('berry_snip_hover_icon_default','HoverBtn · 기본 아이콘', fn()=>self::text('berry_snip_hover_icon_default','이미지 URL'), BERRY_LEAN_SLUG,'tab_snippets');
    add_settings_field('berry_snip_hover_icon_hover','HoverBtn · 호버 아이콘',   fn()=>self::text('berry_snip_hover_icon_hover','이미지 URL'), BERRY_LEAN_SLUG,'tab_snippets');
    add_settings_field('berry_snip_btn_active_bg','그룹버튼 · 활성 BG',           fn()=>self::color('berry_snip_btn_active_bg','#5CBD55'), BERRY_LEAN_SLUG,'tab_snippets');
    add_settings_field('berry_snip_btn_active_fg','그룹버튼 · 활성 FG',           fn()=>self::color('berry_snip_btn_active_fg','#FFFFFF'), BERRY_LEAN_SLUG,'tab_snippets');
    add_settings_field('berry_snip_metform_notice','Metform · 안내 문구',         fn()=>self::text('berry_snip_metform_notice',"(*출산 택일을 하셨던 분은 20만 원을 입금하시면 최종 접수 완료됩니다)"), BERRY_LEAN_SLUG,'tab_snippets');
  }

  private static function fields_seo(){
    add_settings_field('seo_help','<em>안내</em>', fn()=>print('<p>검증 메타, 기본 description, OG, canonical 출력. canonical 미입력 시 <code>home_url("/")</code> 사용.</p>'), BERRY_LEAN_SLUG,'tab_seo');
    add_settings_field('berry_naver_verify','네이버 site verification', fn()=>self::text('berry_naver_verify','예: 492eea7...','width:420px;'), BERRY_LEAN_SLUG,'tab_seo');
    add_settings_field('berry_google_verify','구글 site verification', fn()=>self::text('berry_google_verify','예: 6Ph-ykqC...','width:420px;'), BERRY_LEAN_SLUG,'tab_seo');
    add_settings_field('berry_meta_description','기본 meta description', fn()=>self::text('berry_meta_description','사이트 공통 설명문','width:600px;'), BERRY_LEAN_SLUG,'tab_seo');
    add_settings_field('berry_og_title','OG Title(기본)', fn()=>self::text('berry_og_title','예: 청은좋은이름연구소','width:420px;'), BERRY_LEAN_SLUG,'tab_seo');
    add_settings_field('berry_og_image','OG Image URL',  fn()=>self::text('berry_og_image','절대경로 이미지 URL','width:600px;'), BERRY_LEAN_SLUG,'tab_seo');
    add_settings_field('berry_canonical','Canonical URL',fn()=>self::text('berry_canonical',home_url('/'),'width:600px;'), BERRY_LEAN_SLUG,'tab_seo');
  }

  /** 페이지 렌더(Tabs) */
  public static function render_page(){ ?>
    <div class="wrap berry-lean">
      <h1>Berry Lean – 사이트 토큰 & 유틸</h1>
      <form method="post" action="options.php">
        <?php settings_fields(BERRY_LEAN_OPT); ?>
        <div class="bl-tabs">
          <nav class="bl-tabnav">
            <a href="#tab_colors"  class="active">색상/레이아웃</a>
            <a href="#tab_fonts">폰트</a>
            <a href="#tab_brand">브랜드/추가변수</a>
            <a href="#tab_snippets">스니펫</a>
            <a href="#tab_seo">SEO</a>
          </nav>
          <section id="tab_colors"  class="bl-tabpanel active"><?php do_settings_sections(BERRY_LEAN_SLUG); // 전체 섹션 출력, JS가 탭만 보여줌 ?></section>
          <section id="tab_fonts"   class="bl-tabpanel"></section>
          <section id="tab_brand"   class="bl-tabpanel"></section>
          <section id="tab_snippets" class="bl-tabpanel"></section>
          <section id="tab_seo"     class="bl-tabpanel"></section>
        </div>
        <?php submit_button(); ?>
      </form>
    </div>
  <?php }
}
