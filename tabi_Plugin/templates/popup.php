<?php
if (!defined('ABSPATH')) exit;

$button     = get_option('tabi_popup_button', 'Click here');
$button_url = get_option('tabi_popup_button_url', '#');
$bg_color   = get_option('tabi_popup_bg_color', '#b8a999');
$position   = get_option('tabi_popup_position', 'center');
$width_option = get_option('tabi_popup_width', 'default');

$headline = get_option('tabi_popup_headline');
$headline_font_size = get_option('tabi_popup_headline_font_size', 28);
$headline_font_size_mobile = get_option('tabi_popup_headline_font_size_mobile', 24);
$headline_font_family = get_option('tabi_popup_headline_font_family', 'sans-serif');
$headline_font_weight = get_option('tabi_popup_headline_font_weight', '500');
$headline_font_color = get_option('tabi_popup_headline_font_color', '#000000');
$headline_alignment = get_option('tabi_popup_headline_alignment', 'center');
$headline_alignment_mobile = get_option('tabi_popup_headline_alignment_mobile', 'center');

$paragraph = get_option('tabi_popup_paragraph');
$paragraph_font_size = get_option('tabi_popup_paragraph_font_size', 16);
$paragraph_font_size_mobile = get_option('tabi_popup_paragraph_font_size_mobile', 14);
$paragraph_font_family = get_option('tabi_popup_paragraph_font_family', 'sans-serif');
$paragraph_font_weight = get_option('tabi_popup_paragraph_font_weight', '400');
$paragraph_font_color = get_option('tabi_popup_paragraph_font_color', '#333333');
$paragraph_alignment = get_option('tabi_popup_paragraph_alignment', 'center');
$paragraph_alignment_mobile = get_option('tabi_popup_paragraph_alignment_mobile', 'center');

// Ensure a valid color if the option is empty in the database
if (empty($bg_color)) {
    $bg_color = '#b8a999';
}

$position_class = ($position === 'bottom') ? 'tabi-popup-bottom' : 'tabi-popup-center';
$width_class = ($width_option === 'full') ? 'tabi-popup-full' : '';
?>

<style>
    /* Dynamic Styles */
    .tabi-popup-headline {
        font-family: <?php echo esc_attr($headline_font_family); ?>;
        font-size: <?php echo intval($headline_font_size); ?>px;
        font-weight: <?php echo esc_attr($headline_font_weight); ?>;
        color: <?php echo sanitize_hex_color($headline_font_color); ?>;
        text-align: <?php echo esc_attr($headline_alignment); ?>;
        margin: 0 0 10px 0;
    }
    .tabi-popup-paragraph {
        font-family: <?php echo esc_attr($paragraph_font_family); ?>;
        font-size: <?php echo intval($paragraph_font_size); ?>px;
        font-weight: <?php echo esc_attr($paragraph_font_weight); ?>;
        color: <?php echo sanitize_hex_color($paragraph_font_color); ?>;
        text-align: <?php echo esc_attr($paragraph_alignment); ?>;
        margin: 0 0 20px 0;
    }

    @media (max-width: 768px) {
        .tabi-popup-headline {
            font-size: <?php echo intval($headline_font_size_mobile); ?>px;
            text-align: <?php echo esc_attr($headline_alignment_mobile); ?>;
        }
        .tabi-popup-paragraph {
            font-size: <?php echo intval($paragraph_font_size_mobile); ?>px;
            text-align: <?php echo esc_attr($paragraph_alignment_mobile); ?>;
        }
    }

    .tabi-popup-overlay.tabi-popup-center {
        align-items: center;
        justify-content: center;
    }
    .tabi-popup-overlay.tabi-popup-bottom {
        align-items: flex-end;
        justify-content: center;
    }

    /* Base Animation */
    .tabi-popup-content {
        opacity: 0;
        transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
        transform: translateY(20px); /* Slight movement towards center */
    }

    .tabi-popup-overlay.tabi-popup-bottom .tabi-popup-content {
        margin-bottom: 20px;
        transform: translateY(100%); /* Slide from bottom */
    }
    
    .tabi-popup-content.tabi-popup-full {
        width: 100%;
        max-width: 100%;
        border-radius: 0;
        box-sizing: border-box;
    }
    .tabi-popup-overlay.tabi-popup-bottom .tabi-popup-content.tabi-popup-full {
        margin-bottom: 0;
    }

    .tabi-popup-overlay.active .tabi-popup-content {
        opacity: 1;
        transform: translateY(0);
    }
</style>
<div id="tabi-popup-overlay" class="tabi-popup-overlay <?php echo esc_attr($position_class); ?>" style="display:none;">
    <div class="tabi-popup-content <?php echo esc_attr($width_class); ?>" style="background-color: <?php echo esc_attr($bg_color); ?>;">
        <span class="tabi-popup-close">&times;</span>
        <div class="tabi-popup-body">
            <?php if (!empty($headline)) : ?>
                <h2 class="tabi-popup-headline"><?php echo esc_html($headline); ?></h2>
            <?php endif; ?>

            <?php if (!empty($paragraph)) : ?>
                <p class="tabi-popup-paragraph"><?php echo nl2br(esc_html($paragraph)); ?></p>
            <?php endif; ?>

            <a href="<?php echo esc_url($button_url); ?>" class="tabi-popup-button"><?php echo esc_html($button); ?></a>
        </div>
    </div>
</div>
