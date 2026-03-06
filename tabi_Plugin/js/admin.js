jQuery(document).ready(function($) {
    // Initialize the WordPress color picker
    $('.tabi-color-picker').wpColorPicker({
        change: function(event, ui) {
            // Trigger change event on the input so our preview updater catches it
            $(event.target).val(ui.color.toString()).trigger('change');
        },
        clear: function() {
            $(this).prev('.wp-color-picker').val('').trigger('change');
        }
    });

    // --- Logic for Toggling Trigger Fields ---

    function toggleTriggerFields() {
        var selectedTrigger = $('input[name="popup_trigger_type"]:checked').val();

        if (selectedTrigger === 'scroll') {
            $('#tabi-scroll-trigger-row').show();
            $('#tabi-delay-trigger-row').hide();
        } else if (selectedTrigger === 'delay') {
            $('#tabi-scroll-trigger-row').hide();
            $('#tabi-delay-trigger-row').show();
        }
    }

    // Run on page load to set the initial state
    toggleTriggerFields();

    // Run when the radio button selection changes
    $('input[name="popup_trigger_type"]').on('change', toggleTriggerFields);

    // --- Live Preview Logic ---

    function updatePreview() {
        // Get values
        var btnText = $('input[name="popup_button"]').val();
        var bgColor = $('input[name="popup_bg_color"]').val();
        var position = $('input[name="popup_position"]:checked').val();
        var width = $('input[name="popup_width"]:checked').val();
        
        var headline = $('input[name="popup_headline"]').val();
        var headlineSize = $('input[name="popup_headline_font_size"]').val();
        var headlineSizeMobile = $('input[name="popup_headline_font_size_mobile"]').val();
        var headlineFontFamily = $('select[name="popup_headline_font_family"]').val();
        var headlineWeight = $('select[name="popup_headline_font_weight"]').val();
        var headlineColor = $('input[name="popup_headline_font_color"]').val();
        var headlineAlign = $('input[name="popup_headline_alignment"]:checked').val();
        var headlineAlignMobile = $('input[name="popup_headline_alignment_mobile"]:checked').val();

        var paragraph = $('textarea[name="popup_paragraph"]').val();
        var paragraphSize = $('input[name="popup_paragraph_font_size"]').val();
        var paragraphSizeMobile = $('input[name="popup_paragraph_font_size_mobile"]').val();
        var paragraphFontFamily = $('select[name="popup_paragraph_font_family"]').val();
        var paragraphWeight = $('select[name="popup_paragraph_font_weight"]').val();
        var paragraphColor = $('input[name="popup_paragraph_font_color"]').val();
        var paragraphAlign = $('input[name="popup_paragraph_alignment"]:checked').val();
        var paragraphAlignMobile = $('input[name="popup_paragraph_alignment_mobile"]:checked').val();

        // Update Text
        var headlineHtml = headline ? '<h2 class="tabi-popup-headline">' + headline + '</h2>' : '';
        var formattedParagraph = paragraph.replace(/\n/g, '<br>');
        var paragraphHtml = paragraph ? '<p class="tabi-popup-paragraph">' + formattedParagraph + '</p>' : '';
        
        $('#preview-text-container').html(headlineHtml + paragraphHtml);

        var css = `
            #preview-content .tabi-popup-headline {
                font-family: ${headlineFontFamily};
                font-size: ${headlineSize}px;
                font-weight: ${headlineWeight};
                color: ${headlineColor};
                text-align: ${headlineAlign};
                margin: 0 0 10px 0;
            }
            #preview-content .tabi-popup-paragraph {
                font-family: ${paragraphFontFamily};
                font-size: ${paragraphSize}px;
                font-weight: ${paragraphWeight};
                color: ${paragraphColor};
                text-align: ${paragraphAlign};
                margin: 0 0 20px 0;
            }
            /* Mobile Overrides for Preview */
            #tabi-popup-preview-wrapper.mobile-view #preview-content .tabi-popup-headline {
                font-size: ${headlineSizeMobile}px;
                text-align: ${headlineAlignMobile};
            }
            #tabi-popup-preview-wrapper.mobile-view #preview-content .tabi-popup-paragraph {
                font-size: ${paragraphSizeMobile}px;
                text-align: ${paragraphAlignMobile};
            }
        `;
        
        // Inject CSS
        var $style = $('#tabi-preview-styles');
        if ($style.length === 0) {
            $style = $('<style id="tabi-preview-styles"></style>');
            $('body').append($style);
        }
        $style.text(css);

        // Update Button
        $('#preview-button').text(btnText);

        // Update Background Color
        $('#preview-content').css('background-color', bgColor);

        // Update Position Class
        var $overlay = $('#preview-overlay');
        $overlay.removeClass('tabi-popup-center tabi-popup-bottom');
        if (position === 'bottom') {
            $overlay.addClass('tabi-popup-bottom');
        } else {
            $overlay.addClass('tabi-popup-center');
        }

        // Update Width Class
        var $content = $('#preview-content');
        $content.removeClass('tabi-popup-full');
        if (width === 'full') {
            $content.addClass('tabi-popup-full');
        }
    }

    // Bind events to inputs for real-time updates
    $('input[name="popup_headline"], textarea[name="popup_paragraph"], input[name="popup_button"]').on('input', updatePreview);
    $('input[name="popup_headline_font_size"], input[name="popup_paragraph_font_size"], input[name="popup_headline_font_size_mobile"], input[name="popup_paragraph_font_size_mobile"]').on('input', updatePreview);
    $('select, input[type="radio"]').on('change', updatePreview);
    
    $('input[name="popup_position"], input[name="popup_width"]').on('change', updatePreview);
    $('.tabi-color-picker').on('change', updatePreview);

    // Initial update
    updatePreview();
    
    // Force active class on preview overlay to make content visible
    $('#preview-overlay').addClass('active');

    // --- Mobile Preview Toggle ---
    $('#tabi-preview-mobile').on('click', function() {
        $('#tabi-popup-preview-wrapper').addClass('mobile-view');
        $(this).addClass('active');
        $('#tabi-preview-desktop').removeClass('active');
    });

    $('#tabi-preview-desktop').on('click', function() {
        $('#tabi-popup-preview-wrapper').removeClass('mobile-view');
        $(this).addClass('active');
        $('#tabi-preview-mobile').removeClass('active');
    });
});