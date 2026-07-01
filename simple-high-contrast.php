<?php
/**
 * Plugin Name:       Prosty Kontrast i Zmiana Rozmiaru Czcionki
 * Description:       Pływający pasek dostępności z wysokim kontrastem (żółty na ciemnym tle) i regulacją rozmiaru czcionki. Możliwość pełnej konfiguracji z poziomu kokpitu.
 * Version:           1.3.2
 * Author:            Grok
 * License:           GPL v2 or later
 */

if (!defined('WPINC')) {
    die;
}

class Simple_High_Contrast {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('wp_footer', array($this, 'render_floating_toolbar'));
    }

    public function add_settings_page() {
        add_options_page(
            'Ustawienia Dostępności',
            'Dostępność (Kontrast)',
            'manage_options',
            'simple-high-contrast',
            array($this, 'settings_page_html')
        );
    }

    public function settings_init() {
        register_setting(
            'simple_high_contrast_options', 
            'simple_high_contrast_settings',
            array($this, 'sanitize_settings')
        );

        add_settings_section('main_section', 'Ustawienia Paska Dostępności', null, 'simple-high-contrast');

        // Główne opcje paska
        add_settings_field('position', 'Pozycja paska na ekranie', array($this, 'position_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('show_contrast', 'Włącz przycisk kontrastu', array($this, 'show_contrast_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('show_font', 'Włącz przyciski czcionki (+A -A)', array($this, 'show_font_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('show_reset', 'Włącz przycisk resetu czcionki', array($this, 'show_reset_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('icon_bg', 'Kolor tła paska', array($this, 'icon_bg_field'), 'simple-high-contrast', 'main_section');

        // Personalizacja tekstów
        add_settings_field('title_text', 'Nagłówek paska', array($this, 'title_text_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('contrast_text', 'Tekst przycisku kontrastu', array($this, 'contrast_text_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('font_up_text', 'Tekst powiększenia czcionki', array($this, 'font_up_text_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('font_down_text', 'Tekst pomniejszenia czcionki', array($this, 'font_down_text_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('font_reset_text', 'Tekst resetu czcionki', array($this, 'font_reset_text_field'), 'simple-high-contrast', 'main_section');
    }

    // Funkcja oczyszczająca dane przesyłane z formularza
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['position'])) {
            $sanitized['position'] = ($input['position'] === 'right') ? 'right' : 'left';
        }
        
        $sanitized['show_contrast'] = isset($input['show_contrast']) ? '1' : '0';
        $sanitized['show_font'] = isset($input['show_font']) ? '1' : '0';
        $sanitized['show_reset'] = isset($input['show_reset']) ? '1' : '0';
        
        if (isset($input['icon_bg'])) {
            $color = sanitize_hex_color($input['icon_bg']);
            $sanitized['icon_bg'] = $color ? $color : '#000000';
        }

        $sanitized['title_text'] = isset($input['title_text']) ? sanitize_text_field($input['title_text']) : 'Dostępność';
        $sanitized['contrast_text'] = isset($input['contrast_text']) ? sanitize_text_field($input['contrast_text']) : '⚡ Kontrast';
        $sanitized['font_up_text'] = isset($input['font_up_text']) ? sanitize_text_field($input['font_up_text']) : '+A Większa';
        $sanitized['font_down_text'] = isset($input['font_down_text']) ? sanitize_text_field($input['font_down_text']) : '-A Mniejsza';
        $sanitized['font_reset_text'] = isset($input['font_reset_text']) ? sanitize_text_field($input['font_reset_text']) : 'A Domyślna';
        
        return $sanitized;
    }

    /* --- Pola formularza w kokpicie --- */

    public function position_field() {
        $options = get_option('simple_high_contrast_settings');
        $position = $options['position'] ?? 'left';
        ?>
<select name="simple_high_contrast_settings[position]">
    <option value="left" <?php selected($position, 'left'); ?>>Lewa strona ekranu</option>
    <option value="right" <?php selected($position, 'right'); ?>>Prawa strona ekranu</option>
</select>
<?php
    }

    public function show_contrast_field() {
        $options = get_option('simple_high_contrast_settings');
        $checked = $options['show_contrast'] ?? '1';
        ?>
<input type="checkbox" name="simple_high_contrast_settings[show_contrast]" value="1" <?php checked($checked, '1'); ?> />
<label>Pokaż przycisk przełączania kontrastu (Żółty na ciemnym tle)</label>
<?php
    }

    public function show_font_field() {
        $options = get_option('simple_high_contrast_settings');
        $checked = $options['show_font'] ?? '1';
        ?>
<input type="checkbox" name="simple_high_contrast_settings[show_font]" value="1" <?php checked($checked, '1'); ?> />
<label>Pokaż przyciski do regulacji rozmiaru czcionki</label>
<?php
    }

    public function show_reset_field() {
        $options = get_option('simple_high_contrast_settings');
        $checked = $options['show_reset'] ?? '1';
        ?>
<input type="checkbox" name="simple_high_contrast_settings[show_reset]" value="1" <?php checked($checked, '1'); ?> />
<label>Pokaż przycisk przywracania domyślnego rozmiaru czcionki</label>
<?php
    }

    public function icon_bg_field() {
        $options = get_option('simple_high_contrast_settings');
        $color = $options['icon_bg'] ?? '#000000';
        ?>
<input type="color" name="simple_high_contrast_settings[icon_bg]" value="<?php echo esc_attr($color); ?>" />
<?php
    }

    public function title_text_field() {
        $options = get_option('simple_high_contrast_settings');
        $text = $options['title_text'] ?? 'Dostępność';
        ?>
<input type="text" name="simple_high_contrast_settings[title_text]" value="<?php echo esc_attr($text); ?>"
    class="regular-text" />
<?php
    }

    public function contrast_text_field() {
        $options = get_option('simple_high_contrast_settings');
        $text = $options['contrast_text'] ?? '⚡ Kontrast';
        ?>
<input type="text" name="simple_high_contrast_settings[contrast_text]" value="<?php echo esc_attr($text); ?>"
    class="regular-text" />
<?php
    }

    public function font_up_text_field() {
        $options = get_option('simple_high_contrast_settings');
        $text = $options['font_up_text'] ?? '+A Większa';
        ?>
<input type="text" name="simple_high_contrast_settings[font_up_text]" value="<?php echo esc_attr($text); ?>"
    class="regular-text" />
<?php
    }

    public function font_down_text_field() {
        $options = get_option('simple_high_contrast_settings');
        $text = $options['font_down_text'] ?? '-A Mniejsza';
        ?>
<input type="text" name="simple_high_contrast_settings[font_down_text]" value="<?php echo esc_attr($text); ?>"
    class="regular-text" />
<?php
    }

    public function font_reset_text_field() {
        $options = get_option('simple_high_contrast_settings');
        $text = $options['font_reset_text'] ?? 'A Domyślna';
        ?>
<input type="text" name="simple_high_contrast_settings[font_reset_text]" value="<?php echo esc_attr($text); ?>"
    class="regular-text" />
<?php
    }

    public function settings_page_html() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
<div class="wrap">
    <h1>Ustawienia paska dostępności i kontrastu</h1>
    <p>Tutaj możesz spersonalizować wygląd oraz etykiety przycisków wyświetlanych na Twojej stronie.</p>
    <form action="options.php" method="post">
        <?php
                settings_fields('simple_high_contrast_options');
                do_settings_sections('simple-high-contrast');
                submit_button('Zapisz ustawienia');
                ?>
    </form>
    <p><strong>Uwaga:</strong> Pasek dostępności jest automatycznie ładowany na części frontowej strony dla wszystkich
        odwiedzających.</p>
</div>
<?php
    }

    /* --- Prezentacja paska po stronie użytkownika --- */

    public function render_floating_toolbar() {
        $options = get_option('simple_high_contrast_settings');
        
        $position      = $options['position'] ?? 'left';
        $show_contrast = $options['show_contrast'] ?? '1';
        $show_font     = $options['show_font'] ?? '1';
        $show_reset    = $options['show_reset'] ?? '1';
        $icon_bg       = $options['icon_bg'] ?? '#000000';

        // Indywidualne nazwy przycisków
        $title_text      = $options['title_text'] ?? 'Dostępność';
        $contrast_text   = $options['contrast_text'] ?? '⚡ Kontrast';
        $font_up_text    = $options['font_up_text'] ?? '+A Większa';
        $font_down_text  = $options['font_down_text'] ?? '-A Mniejsza';
        $font_reset_text = $options['font_reset_text'] ?? 'A Domyślna';

        $side = ($position === 'right') ? 'right: 15px;' : 'left: 15px;';
        ?>
<style>
#simple-accessibility-toolbar {
    position: fixed;
    top: 50%;
    <?php echo $side;
    ?>transform: translateY(-50%);
    background: <?php echo esc_attr($icon_bg);
    ?>;
    color: #ffff00;
    padding: 8px;
    border-radius: 6px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.4);
    z-index: 99999;
    font-size: 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    border: 1.5px solid #ffff00;
    min-width: 110px;
    box-sizing: border-box;
}

#simple-accessibility-toolbar strong {
    color: #ffff00;
    font-size: 11px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 2px;
}

#simple-accessibility-toolbar button {
    background: #222;
    color: #ffff00;
    border: 1px solid #ffff00;
    padding: 6px 8px;
    cursor: pointer;
    border-radius: 4px;
    font-size: 11px;
    width: 100%;
    box-sizing: border-box;
    text-align: center;
    white-space: nowrap;
    transition: background 0.2s ease;
}

#simple-accessibility-toolbar button:hover {
    background: #333;
}
</style>

<div id="simple-accessibility-toolbar">
    <strong><?php echo esc_html($title_text); ?></strong>

    <?php if ($show_contrast) : ?>
    <button onclick="toggleHighContrast()"><?php echo esc_html($contrast_text); ?></button>
    <?php endif; ?>

    <?php if ($show_font) : ?>
    <button onclick="changeFontSize(0.1)"><?php echo esc_html($font_up_text); ?></button>
    <button onclick="changeFontSize(-0.1)"><?php echo esc_html($font_down_text); ?></button>
    <?php endif; ?>

    <?php if ($show_font && $show_reset) : ?>
    <button onclick="resetFontSize()"><?php echo esc_html($font_reset_text); ?></button>
    <?php endif; ?>
</div>

<script>
let fontScale = 1.0;

function toggleHighContrast() {
    document.documentElement.classList.toggle('simple-high-contrast-active');
    localStorage.setItem('highContrastMode', document.documentElement.classList.contains(
        'simple-high-contrast-active'));
}

function changeFontSize(delta) {
    fontScale = Math.max(0.6, Math.min(2.0, fontScale + delta));
    document.documentElement.style.fontSize = (fontScale * 100) + '%';
    localStorage.setItem('fontScale', fontScale);
}

function resetFontSize() {
    fontScale = 1.0;
    document.documentElement.style.fontSize = '100%';
    localStorage.setItem('fontScale', fontScale);
}

window.addEventListener('load', () => {
    if (localStorage.getItem('highContrastMode') === 'true') {
        document.documentElement.classList.add('simple-high-contrast-active');
    }
    const saved = localStorage.getItem('fontScale');
    if (saved) {
        fontScale = parseFloat(saved);
        document.documentElement.style.fontSize = (fontScale * 100) + '%';
    }
});
</script>

<style>
.simple-high-contrast-active,
.simple-high-contrast-active * {
    background-color: #111111 !important;
    color: #ffff00 !important;
}

.simple-high-contrast-active a {
    color: #ffff66 !important;
}
</style>
<?php
    }
}

new Simple_High_Contrast();