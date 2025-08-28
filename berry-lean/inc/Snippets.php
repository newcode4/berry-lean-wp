<?php
namespace BerryLean;

/**
 * 스니펫 레지스트리
 * - btnGroup: 옵션(선택자/활성BG/FG) 노출
 * - navfix  : 옵션 없음
 * - pageTags: 옵션 없음 (Page에 태그 + 관리자 필터)
 */
class Snippets {
  public static function registry(): array {
    return [
      'btnGroup' => [
        'label' => '그룹버튼 현재 페이지 강조(JS)',
        'css'   => null,
        'js'    => function(){
          $sel = trim((string) get_option('berry_snip_btn_selector', '.blox_btn_group.uc-items-wrapper'));
          $bg  = get_option('berry_snip_btn_active_bg', '#5CBD55');
          $fg  = get_option('berry_snip_btn_active_fg', '#FFFFFF');
          $sel_js = json_encode($sel); // 안전
          return <<<JS
document.addEventListener('DOMContentLoaded', function () {
  var currentPath = location.pathname.replace(/\\/$/,'/');
  document.querySelectorAll({$sel_js}).forEach(function(group){
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
      'pageTags' => [
        'label' => 'Page에도 태그 + 관리자 필터',
        'css'   => null,
        'js'    => null,
      ],
    ];
  }

  /** 선택된 스니펫만 적용 */
  public static function apply_selected(array $ids){
    $reg = self::registry();
    foreach ($ids as $id) {
      if (!isset($reg[$id])) continue;
      if (!empty($reg[$id]['css'])) {
        $css = is_callable($reg[$id]['css']) ? call_user_func($reg[$id]['css']) : $reg[$id]['css'];
        if ($css) wp_add_inline_style(BERRY_LEAN_STYLE, $css);
      }
      if (!empty($reg[$id]['js'])) {
        $js  = is_callable($reg[$id]['js'])  ? call_user_func($reg[$id]['js'])  : $reg[$id]['js'];
        if ($js)  wp_add_inline_script(BERRY_LEAN_RT, $js);
      }
    }
  }
}
