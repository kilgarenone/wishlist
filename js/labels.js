(function($) {

    // first thing check if placeholder was supported in user's browser
    $(function() {
        if (document.createElement("input").placeholder !== undefined) {
            $('#signup_form :input').each(function() { hideLabel.call(this); });
        }
        else{

            $('#signup_form').on('cut', 'input', toggleLabel);
             $('#signup_form').on('keydown', 'input', toggleLabel);
            $('#signup_form').on('paste', 'input', toggleLabel);

            $('#signup_form').on('focusin', 'input', function() {
                $(this).prev('span').css('color', '#ccc');
            });
            $('#signup_form').on('focusout', 'input', function() {
                $(this).prev('span').css('color', '#999');
            });
            
        }
    });


    function hideLabel() {
        var input = $(this);

        setTimeout(function() {
            
            input.prev('span').css('visibility', 'hidden');
               
        }, 0);
    }


    function toggleLabel(){
         var input = $(this);

        setTimeout(function() {
            if (!input.val()) {
                input.prev('span').css('visibility', '');
            }
            else {
                 input.prev('span').css('visibility', 'hidden');
            }
        }, 0);
    }



})(jQuery);

