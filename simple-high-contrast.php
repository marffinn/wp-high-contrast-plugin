<?php
/**
 * Plugin Name:       Prosty Kontrast i Zmiana Rozmiaru Czcionki
 * Description:       Pływający pasek dostępności z wysokim kontrastem (żółty na ciemnym tle) i regulacją rozmiaru czcionki. Możliwość pełnej konfiguracji z poziomu kokpitu.
 * Version:           1.3.5
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

    private function get_admin_language() {
        $options = get_option('simple_high_contrast_settings');
        $language = $options['admin_language'] ?? 'pl';
        $allowed = array('pl', 'en', 'de', 'ru', 'zh');

        return in_array($language, $allowed, true) ? $language : 'pl';
    }

    private function get_admin_strings() {
        $language = $this->get_admin_language();

        $translations = array(
            'pl' => array(
                'page_title' => 'Ustawienia paska dostępności i kontrastu',
                'page_description' => 'Tutaj możesz spersonalizować wygląd oraz etykiety przycisków wyświetlanych na Twojej stronie.',
                'save_button' => 'Zapisz ustawienia',
                'notice_title' => 'Uwaga:',
                'notice_text' => 'Pasek dostępności jest automatycznie ładowany na części frontowej strony dla wszystkich odwiedzających.',
                'language_label' => 'Język panelu administracyjnego',
                'position_label' => 'Pozycja paska na ekranie',
                'show_contrast_label' => 'Włącz przycisk kontrastu',
                'show_font_label' => 'Włącz przyciski czcionki (+A -A)',
                'show_reset_label' => 'Włącz przycisk resetu czcionki',
                'icon_bg_label' => 'Kolor tła paska',
                'title_text_label' => 'Nagłówek paska',
                'contrast_text_label' => 'Tekst przycisku kontrastu',
                'font_up_text_label' => 'Tekst powiększenia czcionki',
                'font_down_text_label' => 'Tekst pomniejszenia czcionki',
                'font_reset_text_label' => 'Tekst resetu czcionki',
                'position_left' => 'Lewa strona ekranu',
                'position_right' => 'Prawa strona ekranu',
                'show_contrast_help' => 'Pokaż przycisk przełączania kontrastu (Żółty na ciemnym tle)',
                'show_font_help' => 'Pokaż przyciski do regulacji rozmiaru czcionki',
                'show_reset_help' => 'Pokaż przycisk przywracania domyślnego rozmiaru czcionki'
            ),
            'en' => array(
                'page_title' => 'Accessibility Toolbar Settings',
                'page_description' => 'Here you can customize the appearance and button labels shown on your website.',
                'save_button' => 'Save settings',
                'notice_title' => 'Note:',
                'notice_text' => 'The accessibility toolbar is automatically loaded on the front end for all visitors.',
                'language_label' => 'Admin panel language',
                'position_label' => 'Toolbar position on screen',
                'show_contrast_label' => 'Enable the contrast button',
                'show_font_label' => 'Enable font size buttons (+A -A)',
                'show_reset_label' => 'Enable the font reset button',
                'icon_bg_label' => 'Toolbar background color',
                'title_text_label' => 'Toolbar title',
                'contrast_text_label' => 'Contrast button text',
                'font_up_text_label' => 'Font increase text',
                'font_down_text_label' => 'Font decrease text',
                'font_reset_text_label' => 'Font reset text',
                'position_left' => 'Left side of the screen',
                'position_right' => 'Right side of the screen',
                'show_contrast_help' => 'Show the contrast toggle button (yellow on dark background)',
                'show_font_help' => 'Show buttons to adjust the font size',
                'show_reset_help' => 'Show the button to restore the default font size'
            ),
            'de' => array(
                'page_title' => 'Einstellungen der Barrierefreiheitsleiste',
                'page_description' => 'Hier kannst du das Erscheinungsbild und die Schaltflächentexte deiner Website anpassen.',
                'save_button' => 'Einstellungen speichern',
                'notice_title' => 'Hinweis:',
                'notice_text' => 'Die Barrierefreiheitsleiste wird für alle Besucher automatisch im Frontend geladen.',
                'language_label' => 'Sprache des Admin-Panels',
                'position_label' => 'Position der Leiste auf dem Bildschirm',
                'show_contrast_label' => 'Kontrast-Schaltfläche aktivieren',
                'show_font_label' => 'Schriftgrößen-Schaltflächen aktivieren (+A -A)',
                'show_reset_label' => 'Reset-Schaltfläche für die Schriftgröße aktivieren',
                'icon_bg_label' => 'Hintergrundfarbe der Leiste',
                'title_text_label' => 'Titel der Leiste',
                'contrast_text_label' => 'Text der Kontrast-Schaltfläche',
                'font_up_text_label' => 'Text für Schrift vergrößern',
                'font_down_text_label' => 'Text für Schrift verkleinern',
                'font_reset_text_label' => 'Text für Schriftgröße zurücksetzen',
                'position_left' => 'Linke Seite des Bildschirms',
                'position_right' => 'Rechte Seite des Bildschirms',
                'show_contrast_help' => 'Kontrast-Umschalter anzeigen (gelb auf dunklem Hintergrund)',
                'show_font_help' => 'Schaltflächen zur Anpassung der Schriftgröße anzeigen',
                'show_reset_help' => 'Schaltfläche zum Wiederherstellen der Standard-Schriftgröße anzeigen'
            ),
            'ru' => array(
                'page_title' => 'Настройки панели доступности',
                'page_description' => 'Здесь вы можете настроить внешний вид и подписи кнопок, отображаемых на вашем сайте.',
                'save_button' => 'Сохранить настройки',
                'notice_title' => 'Примечание:',
                'notice_text' => 'Панель доступности автоматически загружается на фронтенде для всех посетителей.',
                'language_label' => 'Язык панели администратора',
                'position_label' => 'Положение панели на экране',
                'show_contrast_label' => 'Включить кнопку контраста',
                'show_font_label' => 'Включить кнопки изменения размера шрифта (+A -A)',
                'show_reset_label' => 'Включить кнопку сброса размера шрифта',
                'icon_bg_label' => 'Цвет фона панели',
                'title_text_label' => 'Заголовок панели',
                'contrast_text_label' => 'Текст кнопки контраста',
                'font_up_text_label' => 'Текст увеличения шрифта',
                'font_down_text_label' => 'Текст уменьшения шрифта',
                'font_reset_text_label' => 'Текст сброса шрифта',
                'position_left' => 'Левая сторона экрана',
                'position_right' => 'Правая сторона экрана',
                'show_contrast_help' => 'Показывать кнопку переключения контраста (жёлтый на тёмном фоне)',
                'show_font_help' => 'Показывать кнопки регулировки размера шрифта',
                'show_reset_help' => 'Показывать кнопку восстановления стандартного размера шрифта'
            ),
            'zh' => array(
                'page_title' => '无障碍工具栏设置',
                'page_description' => '您可以在此自定义网站上显示的外观和按钮标签。',
                'save_button' => '保存设置',
                'notice_title' => '注意：',
                'notice_text' => '无障碍工具栏会自动加载到所有访客的前端页面中。',
                'language_label' => '后台面板语言',
                'position_label' => '工具栏在屏幕上的位置',
                'show_contrast_label' => '启用对比度按钮',
                'show_font_label' => '启用字体大小按钮 (+A -A)',
                'show_reset_label' => '启用字体重置按钮',
                'icon_bg_label' => '工具栏背景颜色',
                'title_text_label' => '工具栏标题',
                'contrast_text_label' => '对比度按钮文本',
                'font_up_text_label' => '放大字体文本',
                'font_down_text_label' => '缩小字体文本',
                'font_reset_text_label' => '重置字体文本',
                'position_left' => '屏幕左侧',
                'position_right' => '屏幕右侧',
                'show_contrast_help' => '显示对比度切换按钮（深色背景上的黄色）',
                'show_font_help' => '显示用于调整字体大小的按钮',
                'show_reset_help' => '显示恢复默认字体大小的按钮'
            )
        );

        return $translations[$language] ?? $translations['pl'];
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

        $strings = $this->get_admin_strings();

        add_settings_section('main_section', $strings['page_title'], null, 'simple-high-contrast');

        add_settings_field('admin_language', $strings['language_label'], array($this, 'admin_language_field'), 'simple-high-contrast', 'main_section');

        // Główne opcje paska
        add_settings_field('position', $strings['position_label'], array($this, 'position_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('show_contrast', $strings['show_contrast_label'], array($this, 'show_contrast_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('show_font', $strings['show_font_label'], array($this, 'show_font_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('show_reset', $strings['show_reset_label'], array($this, 'show_reset_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('icon_bg', $strings['icon_bg_label'], array($this, 'icon_bg_field'), 'simple-high-contrast', 'main_section');

        // Personalizacja tekstów
        add_settings_field('title_text', $strings['title_text_label'], array($this, 'title_text_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('contrast_text', $strings['contrast_text_label'], array($this, 'contrast_text_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('font_up_text', $strings['font_up_text_label'], array($this, 'font_up_text_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('font_down_text', $strings['font_down_text_label'], array($this, 'font_down_text_field'), 'simple-high-contrast', 'main_section');
        add_settings_field('font_reset_text', $strings['font_reset_text_label'], array($this, 'font_reset_text_field'), 'simple-high-contrast', 'main_section');
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

        if (isset($input['admin_language'])) {
            $language = sanitize_text_field($input['admin_language']);
            $sanitized['admin_language'] = in_array($language, array('pl', 'en', 'de', 'ru', 'zh'), true) ? $language : 'pl';
        }
        
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

    public function admin_language_field() {
        $options = get_option('simple_high_contrast_settings');
        $language = $options['admin_language'] ?? 'pl';
        $language = in_array($language, array('pl', 'en', 'de', 'ru', 'zh'), true) ? $language : 'pl';
        ?>
<select name="simple_high_contrast_settings[admin_language]">
    <option value="pl" <?php selected($language, 'pl'); ?>>Polski</option>
    <option value="en" <?php selected($language, 'en'); ?>>English</option>
    <option value="de" <?php selected($language, 'de'); ?>>Deutsch</option>
    <option value="ru" <?php selected($language, 'ru'); ?>>Русский</option>
    <option value="zh" <?php selected($language, 'zh'); ?>>中文</option>
</select>
<?php
    }

    public function position_field() {
        $options = get_option('simple_high_contrast_settings');
        $position = $options['position'] ?? 'left';
        $strings = $this->get_admin_strings();
        ?>
<select name="simple_high_contrast_settings[position]">
    <option value="left" <?php selected($position, 'left'); ?>><?php echo esc_html($strings['position_left']); ?></option>
    <option value="right" <?php selected($position, 'right'); ?>><?php echo esc_html($strings['position_right']); ?></option>
</select>
<?php
    }

    public function show_contrast_field() {
        $options = get_option('simple_high_contrast_settings');
        $checked = $options['show_contrast'] ?? '1';
        $strings = $this->get_admin_strings();
        ?>
<input type="checkbox" name="simple_high_contrast_settings[show_contrast]" value="1" <?php checked($checked, '1'); ?> />
<label><?php echo esc_html($strings['show_contrast_help']); ?></label>
<?php
    }

    public function show_font_field() {
        $options = get_option('simple_high_contrast_settings');
        $checked = $options['show_font'] ?? '1';
        $strings = $this->get_admin_strings();
        ?>
<input type="checkbox" name="simple_high_contrast_settings[show_font]" value="1" <?php checked($checked, '1'); ?> />
<label><?php echo esc_html($strings['show_font_help']); ?></label>
<?php
    }

    public function show_reset_field() {
        $options = get_option('simple_high_contrast_settings');
        $checked = $options['show_reset'] ?? '1';
        $strings = $this->get_admin_strings();
        ?>
<input type="checkbox" name="simple_high_contrast_settings[show_reset]" value="1" <?php checked($checked, '1'); ?> />
<label><?php echo esc_html($strings['show_reset_help']); ?></label>
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
        $strings = $this->get_admin_strings();
        ?>
<div class="wrap">
    <h1><?php echo esc_html($strings['page_title']); ?></h1>
    <p><?php echo esc_html($strings['page_description']); ?></p>
    <form action="options.php" method="post">
        <?php
                settings_fields('simple_high_contrast_options');
                do_settings_sections('simple-high-contrast');
                submit_button($strings['save_button']);
                ?>
    </form>
    <p><strong><?php echo esc_html($strings['notice_title']); ?></strong> <?php echo esc_html($strings['notice_text']); ?></p>
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