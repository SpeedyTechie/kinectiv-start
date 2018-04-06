/* Enhance Mouse Focus */

var enhanceMouseFocusActive = false;
var enhanceMouseFocusEnabled = false;
var enhanceMouseFocusElements = $(); // create an empty jQuery object for storing focusable elements


function enhanceMouseFocusUpdate() {
    if (enhanceMouseFocusEnabled) {
        enhanceMouseFocusElements = $('button, input[type="submit"], [tabindex]').not(enhanceMouseFocusElements); // add any new focusable elements
        
        // if an element gets focus due to a mouse click, prevent it from keeping focus
        enhanceMouseFocusElements.mousedown(function() {
            enhanceMouseFocusActive = true;
            setTimeout(function(){
                enhanceMouseFocusActive = false;
            }, 50);
        }).on('focus', function() {
            if (enhanceMouseFocusActive) {
                $(this).blur();
            }
        });
    }
}

function initEnhanceMouseFocus() {
    enhanceMouseFocusEnabled = true;
    enhanceMouseFocusUpdate();
    
    // update focusable elements on Gravity Forms render
    $(document).on('gform_post_render', function() {
        enhanceMouseFocusUpdate();
    });
}



/* General */

$(function() {
    initEnhanceMouseFocus();
});