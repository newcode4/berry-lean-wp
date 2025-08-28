<?php
namespace BerryLean;

/**
 * - 스니펫 레지스트리: id => [label, css, js, tokens]
 * - 토큰 치환: {{token}} → 옵션값/기본값
 */
class Snippets {
  public static function registry(): array {
    return [
      'metform' => [
        'label'  => 'Metform 글로벌 스타일',
        'css'    => function(){
          $notice = get_option('berry_snip_metform_notice', '(*출산 택일을 하셨던 분은 20만 원을 입금하시면 최종 접수 완료됩니다)');
          return <<<CSS
.mf-input{ border-radius:0!important; }
.mf-input-label{ font-size:18px!important; font-family:'Pretendard'; font-weight:600!important; color:#3A4641!important; padding-top:10px; }
.mf-radio-option{ color:#3A4641!important; margin-bottom:5px; }
.form_inport_text .mf-input-label::after{ content:" {$notice}"; margin-top:10px!important; }
.mf-radio-option span{ color:#3A4641!important; font-size:18px!important; font-weight:400!important; }
.mf-radio-option input[type="radio"] + span:before{ top:-1px!important; font-size:14px!important; width:15px!important; margin-right:5px!important; }
.elementor-divider-separator{ width:120%!important; margin-left:-20px!important; margin-right:-20px!important; }
.elementor-button-icon svg{ height:auto; width:2em; padding-right:10px; }
@media (max-width:465px){
  .sub_header{ margin-top:-10px!important; }
  .title_container{ min-height:155px!important; }
}
CSS;
        },
        'js'     => null,
      ],

      'navfix' => [
        'label' => 'Elementor 메뉴 꿀렁 방지',
        'css'   => <<<CSS
.elementor-nav-menu .menu-item-has-children > a{ position:relative; padding-right:18px; }
.elementor-nav-menu .menu-item-has-children > a .sub-arrow{
  position:absolute!important; right:10%; top:65%; transform:translateY(-50%);
  width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center;
}
CSS,
        'js'    => null,
      ],

      'hoverBtn' => [
        'label' => 'Hover 버튼 아이콘 교체',
        'css'   => function(){
          $def = esc_url( get_option('berry_snip_hover_icon_default','https://example.com/default.svg') );
          $hov = esc_url( get_option('berry_snip_hover_icon_hover','https://example.com/hover.svg') );
          return <<<CSS
.hover_button .elementor-button-icon svg{ display:none; }
.hover_button .elementor-button-icon{
  display:inline-block; width:30px; height:30px; margin-right:10px;
  background:url("{$def}") no-repeat center/contain;
}
.hover_button:hover .elementor-button-icon{
  background-image:url("{$hov}");
}
CSS;
        },
        'js'    => null,
      ],

      'btnGroup' => [
        'label' => '그룹버튼 현재 페이지 강조(JS)',
        'css'   => null,
        'js'    => function(){
          $bg = get_option('berry_snip_btn_active_bg', '#5CBD55');
          $fg = get_option('berry_snip_btn_active_fg', '#FFFFFF');
          return <<<JS
document.addEventListener('DOMContentLoaded', function () {
  var currentPath = location.pathname.replace(/\\/$/,'/');
  document.querySelectorAll('.blox_btn_group.uc-items-wrapper').forEach(function(group){
    group.querySelectorAll('a').forEach(function(a){
      try{
        var p = new URL(a.href, location.origin).pathname.replace(/\\/$/,'/');
        if(p === currentPath){
          a.style.setProperty('background-color','{$bg}','important');
          a.style.setProperty('color','{$fg}','important');
          a.style.setProperty('z-index','10','important');
          a.style.setProperty('position','relative','important');
        }
      }catch(e){}
    });
  });
});
JS;
        },
      ],
    ];
  }

  /** 선택된 스니펫만 적용 */
  public static function apply_selected(array $ids){
    $reg = self::registry();
    foreach ($ids as $id) {
      if (!isset($reg[$id])) continue;

      // CSS
      if (!empty($reg[$id]['css'])) {
        $css = is_callable($reg[$id]['css']) ? call_user_func($reg[$id]['css']) : $reg[$id]['css'];
        if (!empty($css)) wp_add_inline_style(BERRY_LEAN_STYLE, $css);
      }
      // JS
      if (!empty($reg[$id]['js'])) {
        $js = is_callable($reg[$id]['js']) ? call_user_func($reg[$id]['js']) : $reg[$id]['js'];
        if (!empty($js)) wp_add_inline_script(BERRY_LEAN_RT, $js);
      }
    }
  }
}
