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
    wp_enqueue_style('berry-lean-admin', BERRY_LEAN_URL.'assets/admin.css', [], '1.2.0');
    wp_enqueue_script('berry-lean-admin', BERRY_LEAN_URL.'assets/admin.js', ['jquery','underscore'], '1.2.0', true);
  }

  /** Settings API 등록 */
  public static function register_settings(){
    // 전역 토큰(:root)
    register_setting(BERRY_LEAN_OPT, 'berry_primary',    ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_primary600', ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_fg',         ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_bg',         ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_muted',      ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_border',     ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_container',  ['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_extra_vars', ['sanitize_callback'=>'wp_kses_post']);

    // 폰트
    register_setting(BERRY_LEAN_OPT, 'berry_font_sans',  ['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_font_title', ['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_font_base_size', ['sanitize_callback'=>'sanitize_text_field']); // 16px
    register_setting(BERRY_LEAN_OPT, 'berry_font_base_lh',   ['sanitize_callback'=>'sanitize_text_field']); // 1.6
    register_setting(BERRY_LEAN_OPT, 'berry_font_track_kr',  ['sanitize_callback'=>'sanitize_text_field']); // -0.04em
    register_setting(BERRY_LEAN_OPT, 'berry_font_track_en',  ['sanitize_callback'=>'sanitize_text_field']); // 0em

    // 스니펫 토글 + 옵션(그룹버튼만)
    register_setting(BERRY_LEAN_OPT, 'berry_snippets', [
      'sanitize_callback'=>function($arr){ if (!is_array($arr)) return []; $out=array_map('sanitize_text_field',$arr); return array_values(array_unique($out)); }
    ]);
    register_setting(BERRY_LEAN_OPT, 'berry_snip_btn_selector',   ['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_snip_btn_active_bg',  ['sanitize_callback'=>'sanitize_hex_color']);
    register_setting(BERRY_LEAN_OPT, 'berry_snip_btn_active_fg',  ['sanitize_callback'=>'sanitize_hex_color']);

    // SEO
    register_setting(BERRY_LEAN_OPT, 'berry_naver_verify',    ['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_google_verify',   ['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_meta_description',['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_og_title',        ['sanitize_callback'=>'sanitize_text_field']);
    register_setting(BERRY_LEAN_OPT, 'berry_og_image',        ['sanitize_callback'=>'esc_url_raw']);
    register_setting(BERRY_LEAN_OPT, 'berry_canonical',       ['sanitize_callback'=>'esc_url_raw']);

    // 컨테이너(고정 3종 + 커스텀 반복자)
    register_setting(BERRY_LEAN_OPT, 'berry_containers', [
      'sanitize_callback'=>[__CLASS__, 'sanitize_containers']
    ]);

    // 섹션(탭 역할)
    foreach (['tab_colors','tab_fonts','tab_containers','tab_snippets','tab_seo'] as $sec){
      add_settings_section($sec, '', function(){}, BERRY_LEAN_SLUG);
    }

    // 필드
    self::fields_colors();
    self::fields_fonts();
    self::fields_containers();
    self::fields_snippets();
    self::fields_seo();
  }

  /** 컨테이너 기본값 */
  public static function default_containers(): array {
    return [
      'header' => [
        'class' => 'header_container',
        'label' => 'Header',
        'py'=>['desktop'=>'40px','tablet'=>'32px','mobile'=>'24px'],
        'px'=>['desktop'=>'16px','tablet'=>'16px','mobile'=>'12px'],
        'gap'=>['desktop'=>'24px','tablet'=>'20px','mobile'=>'16px'],
        'bt'=>0, 'bb'=>0,
      ],
      'body' => [
        'class' => 'body_container',
        'label' => 'Body',
        'py'=>['desktop'=>'40px','tablet'=>'32px','mobile'=>'24px'],
        'px'=>['desktop'=>'16px','tablet'=>'16px','mobile'=>'12px'],
        'gap'=>['desktop'=>'24px','tablet'=>'20px','mobile'=>'16px'],
        'bt'=>0, 'bb'=>0,
      ],
      'title' => [
        'class' => 'title_container',
        'label' => 'Title',
        'py'=>['desktop'=>'32px','tablet'=>'28px','mobile'=>'20px'],
        'px'=>['desktop'=>'0','tablet'=>'0','mobile'=>'0'],
        'gap'=>['desktop'=>'12px','tablet'=>'12px','mobile'=>'10px'],
        'bt'=>0, 'bb'=>1,
      ],
      'custom'=>[] // 사용자 추가
    ];
  }

  /** 컨테이너 sanitize */
  public static function sanitize_containers($arr){
    $def = self::default_containers();
    if (!is_array($arr)) return $def;
    $clean = $def;

    $norm = function($v){ return is_string($v) ? trim($v) : ''; };
    $bool = function($v){ return (int) !!$v; };
    $unit = function($v){ $v = trim((string)$v); return $v===''?'0':$v; };
    $klass= function($v){ $v = preg_replace('~[^a-zA-Z0-9\-_]~','',$v); return $v?:'container'; };

    foreach (['header','body','title'] as $key){
      if (!isset($arr[$key])) continue;
      $src = $arr[$key];

      $clean[$key]['class'] = isset($src['class'])? $klass($src['class']) : $def[$key]['class'];
      $clean[$key]['label'] = $norm($src['label'] ?? $def[$key]['label']);

      foreach (['py','px','gap'] as $grp){
        foreach (['desktop','tablet','mobile'] as $bp){
          $clean[$key][$grp][$bp] = $unit($src[$grp][$bp] ?? $def[$key][$grp][$bp]);
        }
      }
      $clean[$key]['bt'] = $bool($src['bt'] ?? 0);
      $clean[$key]['bb'] = $bool($src['bb'] ?? 0);
    }

    // custom
    $clean['custom'] = [];
    if (isset($arr['custom']) && is_array($arr['custom'])){
      foreach ($arr['custom'] as $it){
        if (empty($it['class'])) continue;
        $row = [
          'class' => $klass($it['class']),
          'label' => $norm($it['label'] ?? ''),
          'py' => ['desktop'=>$unit($it['py']['desktop'] ?? '24px'),
                   'tablet' =>$unit($it['py']['tablet']  ?? '20px'),
                   'mobile' =>$unit($it['py']['mobile']  ?? '16px')],
          'px' => ['desktop'=>$unit($it['px']['desktop'] ?? '16px'),
                   'tablet' =>$unit($it['px']['tablet']  ?? '16px'),
                   'mobile' =>$unit($it['px']['mobile']  ?? '12px')],
          'gap'=> ['desktop'=>$unit($it['gap']['desktop']?? '16px'),
                   'tablet' =>$unit($it['gap']['tablet'] ?? '14px'),
                   'mobile' =>$unit($it['gap']['mobile'] ?? '12px')],
          'bt' => $bool($it['bt'] ?? 0),
          'bb' => $bool($it['bb'] ?? 0),
        ];
        $clean['custom'][] = $row;
      }
    }
    return $clean;
  }

  /** helpers */
  private static function color($id, $default){ $v=esc_attr(get_option($id,$default)); echo "<input type='text' id='{$id}' name='{$id}' value='{$v}' class='berry-color-field' data-default-color='{$default}' />"; }
  private static function text($id, $ph='', $style='width:320px;'){ $v=esc_attr(get_option($id,'')); echo "<input type='text' id='{$id}' name='{$id}' value='{$v}' class='regular-text' placeholder='{$ph}' style='{$style}' />"; }

  private static function fields_colors(){
    add_settings_field('berry_primary','메인(--primary)',          fn()=>self::color('berry_primary','#2F855A'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_primary600','호버(--primary-600)',   fn()=>self::color('berry_primary600','#276749'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_fg','본문(--fg)',                    fn()=>self::color('berry_fg','#222222'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_bg','배경(--bg)',                    fn()=>self::color('berry_bg','#FFFFFF'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_muted','보조(--muted)',              fn()=>self::color('berry_muted','#6B7280'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_border','테두리(--border)',          fn()=>self::color('berry_border','#E5E7EB'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_container','컨테이너 폭(--container)',fn()=>self::text('berry_container','예: 1200px','width:160px;'), BERRY_LEAN_SLUG,'tab_colors');
    add_settings_field('berry_extra_vars','추가 CSS 변수 블록',    fn()=>print('<textarea name="berry_extra_vars" rows="6" class="large-text code" placeholder=":root{ --cta:#F59E0B; }">'.esc_textarea(get_option('berry_extra_vars','')).'</textarea>'), BERRY_LEAN_SLUG,'tab_colors');
  }

  private static function fields_fonts(){
    add_settings_field('berry_font_sans','본문 폰트(--font-sans)',    fn()=>self::text('berry_font_sans',"'Pretendard', system-ui, -apple-system, 'Noto Sans KR', sans-serif",'width:600px;'), BERRY_LEAN_SLUG,'tab_fonts');
    add_settings_field('berry_font_title','타이틀 폰트(--font-title)', fn()=>self::text('berry_font_title',"'Paperlogy','Pretendard', system-ui, -apple-system, 'Noto Sans KR', sans-serif",'width:600px;'), BERRY_LEAN_SLUG,'tab_fonts');
    add_settings_field('berry_font_base_size','기본 글자 크기', fn()=>self::text('berry_font_base_size','예: 16px','width:120px;'), BERRY_LEAN_SLUG,'tab_fonts');
    add_settings_field('berry_font_base_lh','기본 줄높이',     fn()=>self::text('berry_font_base_lh','예: 1.6','width:120px;'), BERRY_LEAN_SLUG,'tab_fonts');
    add_settings_field('berry_font_track_kr','한글 자간(letter-spacing)', fn()=>self::text('berry_font_track_kr','예: -0.04em','width:120px;'), BERRY_LEAN_SLUG,'tab_fonts');
    add_settings_field('berry_font_track_en','영문 자간(letter-spacing)', fn()=>self::text('berry_font_track_en','예: 0em','width:120px;'), BERRY_LEAN_SLUG,'tab_fonts');
  }

  private static function fields_containers(){
    add_settings_field('berry_containers','컨테이너(기본 3종 + 추가)', [__CLASS__, 'render_containers'], BERRY_LEAN_SLUG,'tab_containers');
  }

  /** UI: 컨테이너 에디터 */
  public static function render_containers(){
    $data = get_option('berry_containers');
    if (!$data) $data = self::default_containers();

    $row = function($name, $cfg, $isCustom=false){
      $p = "berry_containers[{$name}]";
      $classVal = esc_attr($cfg['class']);
      $labelVal = esc_attr($cfg['label']);
      $bt = !empty($cfg['bt']) ? 'checked' : '';
      $bb = !empty($cfg['bb']) ? 'checked' : '';
      ?>
      <div class="bl-card" data-name="<?php echo esc_attr($name); ?>">
        <div class="bl-card-head">
          <strong><?php echo $isCustom ? 'Custom' : 'Preset'; ?></strong>
          <?php if ($isCustom): ?>
            <button type="button" class="button-link delete-container">삭제</button>
          <?php endif; ?>
        </div>
        <div class="bl-grid-2">
          <label>클래스명(. 제외)</label>
          <input type="text" name="<?php echo $p; ?>[class]" value="<?php echo $classVal; ?>" placeholder="예: section_container" />
          <label>라벨(설명)</label>
          <input type="text" name="<?php echo $p; ?>[label]" value="<?php echo $labelVal; ?>" placeholder="예: 본문 섹션" />
        </div>

        <div class="bl-break"></div>
        <div class="bl-grid-6">
          <div><em>Desktop Y</em><input type="text" name="<?php echo $p; ?>[py][desktop]" value="<?php echo esc_attr($cfg['py']['desktop']); ?>" placeholder="예: 40px" /></div>
          <div><em>Desktop X</em><input type="text" name="<?php echo $p; ?>[px][desktop]" value="<?php echo esc_attr($cfg['px']['desktop']); ?>" placeholder="예: 16px" /></div>
          <div><em>Desktop gap</em><input type="text" name="<?php echo $p; ?>[gap][desktop]" value="<?php echo esc_attr($cfg['gap']['desktop']); ?>" placeholder="예: 24px" /></div>

          <div><em>Tablet Y</em><input type="text" name="<?php echo $p; ?>[py][tablet]" value="<?php echo esc_attr($cfg['py']['tablet']); ?>" placeholder="예: 32px" /></div>
          <div><em>Tablet X</em><input type="text" name="<?php echo $p; ?>[px][tablet]" value="<?php echo esc_attr($cfg['px']['tablet']); ?>" placeholder="예: 16px" /></div>
          <div><em>Tablet gap</em><input type="text" name="<?php echo $p; ?>[gap][tablet]" value="<?php echo esc_attr($cfg['gap']['tablet']); ?>" placeholder="예: 20px" /></div>

          <div><em>Mobile Y</em><input type="text" name="<?php echo $p; ?>[py][mobile]" value="<?php echo esc_attr($cfg['py']['mobile']); ?>" placeholder="예: 24px" /></div>
          <div><em>Mobile X</em><input type="text" name="<?php echo $p; ?>[px][mobile]" value="<?php echo esc_attr($cfg['px']['mobile']); ?>" placeholder="예: 12px" /></div>
          <div><em>Mobile gap</em><input type="text" name="<?php echo $p; ?>[gap][mobile]" value="<?php echo esc_attr($cfg['gap']['mobile']); ?>" placeholder="예: 16px" /></div>
        </div>

        <div class="bl-grid-2">
          <label><input type="checkbox" name="<?php echo $p; ?>[bt]" value="1" <?php echo $bt; ?> /> 상단 보더</label>
          <label><input type="checkbox" name="<?php echo $p; ?>[bb]" value="1" <?php echo $bb; ?> /> 하단 보더</label>
        </div>
      </div>
      <?php
    };

    echo '<div class="bl-stack">';
    // 기본 3종
    $row('header', $data['header'], false);
    $row('body',   $data['body'],   false);
    $row('title',  $data['title'],  false);

    // 커스텀
    if (!empty($data['custom'])) {
      foreach ($data['custom'] as $i=>$cfg) $row("custom][$i", $cfg, true);
    }
    echo '</div>';

    // 추가 버튼 + 템플릿
    ?>
    <p><button type="button" class="button add-container">컨테이너 추가</button></p>
    <script type="text/html" id="bl-container-template">
      <?php $row('custom][_INDEX_', [
        'class'=>'section_container','label'=>'Custom',
        'py'=>['desktop'=>'24px','tablet'=>'20px','mobile'=>'16px'],
        'px'=>['desktop'=>'16px','tablet'=>'16px','mobile'=>'12px'],
        'gap'=>['desktop'=>'16px','tablet'=>'14px','mobile'=>'12px'],
        'bt'=>0,'bb'=>0
      ], true); ?>
    </script>
    <?php
  }

  private static function fields_snippets(){
    $reg = Snippets::registry();

    // 토글
    add_settings_field('berry_snippets','스니펫 선택', function() use($reg){
      $checked = (array) get_option('berry_snippets',[]);
      echo '<div class="bl-snippet-list">';
      foreach($reg as $id=>$meta){
        $is = in_array($id,$checked,true) ? 'checked' : '';
        printf("<label class='bl-row'><input type='checkbox' class='js-snippet' data-target='opts-%s' name='berry_snippets[]' value='%s' %s/> %s</label>",
          esc_attr($id), esc_attr($id), $is, esc_html($meta['label'])
        );
      }
      echo '</div><hr/>';
    }, BERRY_LEAN_SLUG,'tab_snippets');

    // 옵션(그룹버튼만 표시)
    add_settings_field('opts-btnGroup','그룹버튼 옵션', function(){
      $sel = esc_attr(get_option('berry_snip_btn_selector','.blox_btn_group.uc-items-wrapper'));
      $bg  = esc_attr(get_option('berry_snip_btn_active_bg','#5CBD55'));
      $fg  = esc_attr(get_option('berry_snip_btn_active_fg','#FFFFFF'));
      echo '<div id="opts-btnGroup" class="bl-conditional">';
      echo '<p><label>버튼 그룹 선택자(컨테이너) </label><input type="text" name="berry_snip_btn_selector" value="'.$sel.'" style="width:420px;" placeholder=".blox_btn_group.uc-items-wrapper"/></p>';
      echo '<p><label>활성 BG </label><input type="text" class="berry-color-field" data-default-color="#5CBD55" name="berry_snip_btn_active_bg" value="'.$bg.'" style="width:120px;"/></p>';
      echo '<p><label>활성 FG </label><input type="text" class="berry-color-field" data-default-color="#FFFFFF" name="berry_snip_btn_active_fg" value="'.$fg.'" style="width:120px;"/></p>';
      echo '</div>';
    }, BERRY_LEAN_SLUG,'tab_snippets');
  }

  private static function fields_seo(){
    self::_add('berry_naver_verify','네이버 site verification', fn()=>self::text('berry_naver_verify','예: 492eea7...','width:420px;'), 'tab_seo');
    self::_add('berry_google_verify','구글 site verification', fn()=>self::text('berry_google_verify','예: 6Ph-ykqC...','width:420px;'), 'tab_seo');
    self::_add('berry_meta_description','기본 meta description', fn()=>self::text('berry_meta_description','사이트 공통 설명문','width:600px;'), 'tab_seo');
    self::_add('berry_og_title','OG Title(기본)', fn()=>self::text('berry_og_title','예: 청은좋은이름연구소','width:420px;'), 'tab_seo');
    self::_add('berry_og_image','OG Image URL',  fn()=>self::text('berry_og_image','절대경로 이미지 URL','width:600px;'), 'tab_seo');
    self::_add('berry_canonical','Canonical URL',fn()=>self::text('berry_canonical',home_url('/'),'width:600px;'), 'tab_seo');
  }

  private static function _add($id,$label,$cb,$section){ add_settings_field($id,$label,$cb,BERRY_LEAN_SLUG,$section); }

  /** 페이지 렌더 */
  public static function render_page(){ ?>
    <div class="wrap berry-lean">
      <h1>Berry Lean – 토큰 & 유틸</h1>
      <form method="post" action="options.php">
        <?php settings_fields(BERRY_LEAN_OPT); ?>
        <div class="bl-tabs">
          <nav class="bl-tabnav" role="tablist">
            <button type="button" class="active" data-tab="tab_colors">색상/레이아웃</button>
            <button type="button" data-tab="tab_fonts">폰트</button>
            <button type="button" data-tab="tab_containers">컨테이너</button>
            <button type="button" data-tab="tab_snippets">스니펫</button>
            <button type="button" data-tab="tab_seo">SEO</button>
          </nav>

          <?php
          $out = function($sec){
            echo '<table class="form-table"><tbody>';
            do_settings_fields(BERRY_LEAN_SLUG, $sec);
            echo '</tbody></table>';
          };
          ?>
          <section id="tab_colors" class="bl-tabpanel active"><?php $out('tab_colors'); ?></section>
          <section id="tab_fonts" class="bl-tabpanel"><?php $out('tab_fonts'); ?></section>
          <section id="tab_containers" class="bl-tabpanel"><?php $out('tab_containers'); ?></section>
          <section id="tab_snippets" class="bl-tabpanel"><?php $out('tab_snippets'); ?></section>
          <section id="tab_seo" class="bl-tabpanel"><?php $out('tab_seo'); ?></section>
        </div>
        <?php submit_button(); ?>
      </form>
    </div>
  <?php }
}
