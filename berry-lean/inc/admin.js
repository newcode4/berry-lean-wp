(function($){
  // 탭 스위칭
  $(document).on('click', '.berry-lean .bl-tabnav a', function(e){
    e.preventDefault();
    var href = $(this).attr('href');
    $('.berry-lean .bl-tabnav a').removeClass('active');
    $(this).addClass('active');
    $('.berry-lean .bl-tabpanel').removeClass('active');
    $(href).addClass('active');
    // 처음 렌더 시 모든 섹션이 한 섹션에 출력되므로
    // id에 맞는 섹션 내용만 보이도록 이동
    // (Settings API 특성상 섹션 분리는 PHP에서 한번에 출력 → CSS/JS로 탭화)
  });

  // 컬러피커 라이브 프리뷰(:root 반영)
  function applyPreview(){
    $('.berry-color-field').each(function(){
      var id = this.id, val = $(this).val();
      if (/^#[0-9A-Fa-f]{3,8}$/.test(val)) {
        var cssVar = '--' + id.replace(/^berry_/, '');
        document.documentElement.style.setProperty(cssVar, val);
      }
    });
  }
  $(function(){
    if ($.fn.wpColorPicker) {
      $('.berry-color-field').wpColorPicker({
        change: _.throttle(applyPreview, 80),
        clear: applyPreview
      });
    }
  });
})(jQuery);
