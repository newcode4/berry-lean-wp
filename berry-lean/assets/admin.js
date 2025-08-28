(function($){
  // 탭 show/hide (이동 없음)
  $(document).on('click', '.berry-lean .bl-tabnav button', function(){
    var id = $(this).data('tab');
    $('.berry-lean .bl-tabnav button').removeClass('active');
    $(this).addClass('active');
    $('.berry-lean .bl-tabpanel').removeClass('active');
    $('#'+id).addClass('active');
  });

  // 컬러피커 라이브 미리보기(:root)
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
