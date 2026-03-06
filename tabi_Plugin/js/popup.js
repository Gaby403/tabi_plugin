jQuery(document).ready(function($) {

    var $overlay = $('#tabi-popup-overlay');
    var $closeBtn = $('.tabi-popup-close');
    var popupTriggered = false;

    // Ensure settings exist with default values
    var settings = (typeof tabiSettings !== 'undefined') ? tabiSettings : { scrollPoint: 80, delay: 3000, triggerType: 'scroll' };

    function openPopup() {
        if (popupTriggered) return;
        popupTriggered = true;
        
        // Use css('display', 'flex') instead of .show() to maintain CSS centering
        $overlay.css('display', 'flex');
        setTimeout(function() {
            $overlay.addClass('active');
        }, 10);

        // Track View
        if (settingsettings.ajaxUrl, {
                action: 'tabi_track_view',
                nonce: settings.nonce
            });
        }
    }

    function closePopup() {
        $overlay.removeClass('active');
        setTimeout(function() {
            $overlay.hide();
        }, 300);
    }

    function checkScroll() {
        if (popupTriggered) return;

        var scrollTop = $(window).scrollTop();
        var docHeight = $(document).height();
        var winHeight = $(window).height();
        var scrollableHeight = docHeight - winHeight;

        // If the page is short (no scroll) or scrollableHeight is invalid, open the popup
        if (scrollableHeight <= 0) {
            openPopup();
            return;
        }

        var scrollPercent = 100 * scrollTop / scrollableHeight;
        var triggerPoint = settings.scrollPoint || 80;

        if (scrollPercent >= triggerPoint) {
            openPopup();
        }
    }

    if (settings.triggerType === 'delay') {
        setTimeout(openPopup, settings.delay);
    } else {
        var ticking = false;
        $(window).on('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    checkScroll();
                    ticking = false;
                });
                ticking = true;
            }
        });
        
        // Check on page load (in case it's already scrolled or short)
        checkScroll();
    }

    $closeBtn.on('click', closePopup);
    $overlay.on('click', function(e) {
        if (e.target === this) closePopup();
    });
});