(function ($){
    const isValidUrl = (string) => {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;  
        }
    };
  $('.jpwa-form--search input[name=jpwa_audit]').on('keyup',function (){
    if ($(this).val()!=='') {
        if (isValidUrl($(this).val())) {
          $(this).removeClass('is-invalid').addClass('is-valid');
          $(this).parents('form').removeClass('is-invalid').addClass('is-valid');
		  $(this).next('.form-text').attr('data-hidden',true);
        } else {
          $(this).addClass('is-invalid').removeClass('is-valid');
          $(this).parents('form').addClass('is-invalid').removeClass('is-valid');
		  $(this).next('.form-text').attr('data-hidden',false);
        }
    } else {
      $(this).addClass('is-invalid').removeClass('is-valid');
      $(this).parents('form').addClass('is-invalid').removeClass('is-valid');
    }
  });
})(jQuery)