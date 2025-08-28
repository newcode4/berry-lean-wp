(function($){
  // 탭 show/hide
  $(document).on('click', '.berry-lean .bl-tabnav button', function(){
    var id = $(this).data('tab');
    $('.berry-lean .bl-tabnav button').removeClass('active');
    $(this).addClass('active');
    $('.berry-lean .bl-tabpanel').removeClass('active');
    $('#'+id).addClass('active');
  });

  // 컬러피커 프리뷰(:root)
  function applyPreview(){
    $('.berry-color-field').each(function(){
      var id = this.id, val = $(this).value || $(this).val();
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

  // 스니펫 옵션: 체크되면 관련 필드 show
  function refreshSnippetOptions(){
    $('.js-snippet').each(function(){
      var tgt = $(this).data('target');
      if (!tgt) return;
      $('#'+tgt).toggle( this.checked );
    });
  }
  $(document).on('change', '.js-snippet', refreshSnippetOptions);
  $(refreshSnippetOptions);

  // 컨테이너 추가/삭제
  $(document).on('click', '.add-container', function(){
    var tpl = $('#bl-container-template').html();
    var idx = $('.bl-card[data-name^="custom"]').length;
    tpl = tpl.replace(/\[_INDEX_]/g, '['+idx+']');
    $(this).closest('p').before(tpl);
  });
  $(document).on('click', '.delete-container', function(){
    $(this).closest('.bl-card').remove();
  });
})(jQuery);
