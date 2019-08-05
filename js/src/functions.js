/* Enhance Mouse Focus */

var enhanceMouseFocusActive = false;
var enhanceMouseFocusEnabled = false;
var enhanceMouseFocusElements = $();
var enhanceMouseFocusNewElements = $();


function enhanceMouseFocusUpdate() {
    if (enhanceMouseFocusEnabled) {
        // add any new focusable elements
        enhanceMouseFocusNewElements = $('button, input[type="submit"], input[type="button"], [tabindex]:not(input, textarea)').not(enhanceMouseFocusElements);
        enhanceMouseFocusElements = enhanceMouseFocusElements.add(enhanceMouseFocusNewElements);
        
        // if an element gets focus due to a mouse click, prevent it from keeping focus
        enhanceMouseFocusNewElements.mousedown(function() {
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
    enhanceMouseFocusElements = $();
    enhanceMouseFocusEnabled = true;
    
    enhanceMouseFocusUpdate();
    
    // update focusable elements on Gravity Forms render
    $(document).on('gform_post_render', function() {
        enhanceMouseFocusUpdate();
    });
}



/* FitVids */

function initFitVids() {
    $('.site-content').fitVids();
}



/* General */

$(function() {
    initFitVids();
    initEnhanceMouseFocus();
});
