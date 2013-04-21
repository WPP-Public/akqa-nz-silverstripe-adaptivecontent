(function($) {
    $(document).ready(function() {
        $('#right input[name=action_doPublishChildren]').live('click', function(){
            var form = $('#right form');
            var formAction = form.attr('action') + '?' + $(this).fieldSerialize();

            // @todo TinyMCE coupling
            if(typeof tinyMCE != 'undefined') tinyMCE.triggerSave();

            // Post the data to save
            $.post(formAction, form.formToArray(), function(result){
                // @todo TinyMCE coupling
                tinymce_removeAll();

                $('#right #ModelAdminPanel').html(result);

                if($('#right #ModelAdminPanel form').hasClass('validationerror')) {
                    statusMessage(ss.i18n._t('ModelAdmin.VALIDATIONERROR', 'Validation Error'), 'bad');
                } else {
                    statusMessage('Published children', 'good');
                }

                // Is jQuery.live a solution?
                Behaviour.apply(); // refreshes ComplexTableField
                if(window.onresize) window.onresize();
            }, 'html');

            return false;
        });
    });
})(jQuery);