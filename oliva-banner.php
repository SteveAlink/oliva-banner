<?php
/**
 * OlivaBanner
 *
 * Simple multilingual banner plugin for WonderCMS.
 * Prepared for language files in /languages
 */

global $Wcms;

const OLIVABANNER_PLUGIN_PATH = 'plugins/oliva-banner';
const OLIVABANNER_SETTINGS_FILE = 'plugins/oliva-banner/settings.json';

/**
 * Default plugin settings.
 */
function olivaBannerDefaultSettings()
{
    return [
        'enabled' => true,
        'showCloseButton' => true,
        'rememberDismissal' => true,
        'bannerType' => 'info',
        'bannerPosition' => 'bottom',
        'customCssClass' => '',
        'linkUrl' => '',
        'linkTarget' => '_self'
    ];
}

/**
 * Load settings from JSON file.
 */
function olivaBannerLoadSettings()
{
    $defaults = olivaBannerDefaultSettings();

    if (!file_exists(OLIVABANNER_SETTINGS_FILE)) {
        return $defaults;
    }

    $json = file_get_contents(OLIVABANNER_SETTINGS_FILE);
    $settings = json_decode($json, true);

    if (!is_array($settings)) {
        return $defaults;
    }

    return array_merge($defaults, $settings);
}

/**
 * Save settings to JSON file.
 */
function olivaBannerSaveSettings($settings)
{
    file_put_contents(
        OLIVABANNER_SETTINGS_FILE,
        json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}

/**
 * Basic value helper.
 */
function olivaBannerValue($array, $key, $default = '')
{
    return isset($array[$key]) ? $array[$key] : $default;
}
/**
 * Detect frontend language.
 *
 * Uses, for ISO reasons, the setting of contact-form plugin
 */

private function getLang() {

    $defaultLanguage = 'en_US';
    $language = $defaultLanguage;

    if (isset($Wcms) && is_object($Wcms)) {
        // Check and populate contactFormLanguage, as requested.
        $configuredLanguage = $Wcms->get('config', 'contactFormLanguage');
        if (empty($configuredLanguage) || is_object($configuredLanguage)) {
            $Wcms->set('config', 'contactFormLanguage', $defaultLanguage);
            $configuredLanguage = $defaultLanguage;
        }
        $language = (string) $configuredLanguage;
    }

    // Only allow safe language filenames like en_US, nl_NL, es_ES.
    if (!preg_match('/^[a-z]{2}_[A-Z]{2}$/', $language)) {
        $language = $defaultLanguage;
    }

    return $language;
}

/**
 * Load language file with fallback.
 */
function olivaBannerLoadLanguage()
{
    $language = getLang();
    $file = __DIR__ . '/languages/' . $language . '.ini';

    if (!file_exists($file)) {
        $file = __DIR__ . '/languages/en_US.ini';
    }

    $translations = parse_ini_file($file);

    if (!is_array($translations)) {
        $translations = [];
    }

    return array_merge([
        'bannerText' => 'This is a sample banner.',
        'buttonText' => 'More information',
        'closeText' => 'Close',
        'settingsTitle' => 'OlivaBanner settings',
        'settingsSaved' => 'Settings saved.',
        'enabledLabel' => 'Enable banner',
        'showCloseButtonLabel' => 'Show close button',
        'rememberDismissalLabel' => 'Remember dismissal',
        'bannerTypeLabel' => 'Banner type',
        'bannerPositionLabel' => 'Banner position',
        'linkUrlLabel' => 'Link URL',
        'linkTargetLabel' => 'Link target',
        'customCssClassLabel' => 'Custom CSS class',
        'saveButton' => 'Save settings'
    ], $translations);
}

/**
 * Add stylesheet.
 */
function olivaBannerAssets()
{
    echo '<link rel="stylesheet" href="' . OLIVABANNER_PLUGIN_PATH . '/css/style.css">' . PHP_EOL;
}

/**
 * Render frontend banner.
 */
function olivaBannerRender()
{
    $settings = olivaBannerLoadSettings();

    if (empty($settings['enabled'])) {
        return;
    }

    $t = olivaBannerLoadLanguage();

    $classes = [
        'oliva-banner',
        'oliva-banner--' . htmlspecialchars($settings['bannerType'], ENT_QUOTES, 'UTF-8'),
        'oliva-banner--' . htmlspecialchars($settings['bannerPosition'], ENT_QUOTES, 'UTF-8')
    ];

    if (!empty($settings['customCssClass'])) {
        $classes[] = htmlspecialchars($settings['customCssClass'], ENT_QUOTES, 'UTF-8');
    }

    $remember = !empty($settings['rememberDismissal']) ? 'true' : 'false';

    echo '<div id="oliva-banner" class="' . implode(' ', $classes) . '" data-remember-dismissal="' . $remember . '">';
    echo '<div class="oliva-banner__inner">';
    echo '<span class="oliva-banner__text">' . htmlspecialchars($t['bannerText'], ENT_QUOTES, 'UTF-8') . '</span>';

    if (!empty($settings['linkUrl'])) {
        $target = $settings['linkTarget'] === '_blank' ? '_blank' : '_self';
        echo '<a class="oliva-banner__button" href="' . htmlspecialchars($settings['linkUrl'], ENT_QUOTES, 'UTF-8') . '" target="' . $target . '">';
        echo htmlspecialchars($t['buttonText'], ENT_QUOTES, 'UTF-8');
        echo '</a>';
    }

    if (!empty($settings['showCloseButton'])) {
        echo '<button class="oliva-banner__close" type="button" aria-label="' . htmlspecialchars($t['closeText'], ENT_QUOTES, 'UTF-8') . '">';
        echo '&times;';
        echo '</button>';
    }

    echo '</div>';
    echo '</div>';

    echo '<script>
(function() {
    var banner = document.getElementById("oliva-banner");
    if (!banner) return;

    var remember = banner.getAttribute("data-remember-dismissal") === "true";
    var storageKey = "olivaBannerDismissed";

    if (remember && localStorage.getItem(storageKey) === "1") {
        banner.style.display = "none";
        return;
    }

    var close = banner.querySelector(".oliva-banner__close");
    if (close) {
        close.addEventListener("click", function() {
            banner.style.display = "none";
            if (remember) {
                localStorage.setItem(storageKey, "1");
            }
        });
    }
})();
</script>' . PHP_EOL;
}

/**
 * Render admin settings form.
 */
function olivaBannerSettings()
{
    $settings = olivaBannerLoadSettings();
    $t = olivaBannerLoadLanguage();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['olivaBannerSave'])) {
        $settings['enabled'] = isset($_POST['enabled']);
        $settings['showCloseButton'] = isset($_POST['showCloseButton']);
        $settings['rememberDismissal'] = isset($_POST['rememberDismissal']);
        $settings['bannerType'] = in_array($_POST['bannerType'], ['info', 'success', 'warning'], true) ? $_POST['bannerType'] : 'info';
        $settings['bannerPosition'] = in_array($_POST['bannerPosition'], ['top', 'bottom'], true) ? $_POST['bannerPosition'] : 'bottom';
        $settings['linkUrl'] = trim($_POST['linkUrl'] ?? '');
        $settings['linkTarget'] = in_array($_POST['linkTarget'], ['_self', '_blank'], true) ? $_POST['linkTarget'] : '_self';
        $settings['customCssClass'] = trim($_POST['customCssClass'] ?? '');

        olivaBannerSaveSettings($settings);

        echo '<div class="oliva-banner-admin-message">' . htmlspecialchars($t['settingsSaved'], ENT_QUOTES, 'UTF-8') . '</div>';
    }

    echo '<form method="post" class="oliva-banner-admin-form">';
    echo '<h3>' . htmlspecialchars($t['settingsTitle'], ENT_QUOTES, 'UTF-8') . '</h3>';

    echo '<label><input type="checkbox" name="enabled" ' . (!empty($settings['enabled']) ? 'checked' : '') . '> ' . htmlspecialchars($t['enabledLabel'], ENT_QUOTES, 'UTF-8') . '</label>';
    echo '<label><input type="checkbox" name="showCloseButton" ' . (!empty($settings['showCloseButton']) ? 'checked' : '') . '> ' . htmlspecialchars($t['showCloseButtonLabel'], ENT_QUOTES, 'UTF-8') . '</label>';
    echo '<label><input type="checkbox" name="rememberDismissal" ' . (!empty($settings['rememberDismissal']) ? 'checked' : '') . '> ' . htmlspecialchars($t['rememberDismissalLabel'], ENT_QUOTES, 'UTF-8') . '</label>';

    echo '<label>' . htmlspecialchars($t['bannerTypeLabel'], ENT_QUOTES, 'UTF-8');
    echo '<select name="bannerType">';
    foreach (['info', 'success', 'warning'] as $type) {
        echo '<option value="' . $type . '" ' . ($settings['bannerType'] === $type ? 'selected' : '') . '>' . ucfirst($type) . '</option>';
    }
    echo '</select></label>';

    echo '<label>' . htmlspecialchars($t['bannerPositionLabel'], ENT_QUOTES, 'UTF-8');
    echo '<select name="bannerPosition">';
    foreach (['top', 'bottom'] as $position) {
        echo '<option value="' . $position . '" ' . ($settings['bannerPosition'] === $position ? 'selected' : '') . '>' . ucfirst($position) . '</option>';
    }
    echo '</select></label>';

    echo '<label>' . htmlspecialchars($t['linkUrlLabel'], ENT_QUOTES, 'UTF-8');
    echo '<input type="url" name="linkUrl" value="' . htmlspecialchars($settings['linkUrl'], ENT_QUOTES, 'UTF-8') . '" placeholder="https://example.com"></label>';

    echo '<label>' . htmlspecialchars($t['linkTargetLabel'], ENT_QUOTES, 'UTF-8');
    echo '<select name="linkTarget">';
    echo '<option value="_self" ' . ($settings['linkTarget'] === '_self' ? 'selected' : '') . '>Same window</option>';
    echo '<option value="_blank" ' . ($settings['linkTarget'] === '_blank' ? 'selected' : '') . '>New window</option>';
    echo '</select></label>';

    echo '<label>' . htmlspecialchars($t['customCssClassLabel'], ENT_QUOTES, 'UTF-8');
    echo '<input type="text" name="customCssClass" value="' . htmlspecialchars($settings['customCssClass'], ENT_QUOTES, 'UTF-8') . '"></label>';

    echo '<button type="submit" name="olivaBannerSave" value="1">' . htmlspecialchars($t['saveButton'], ENT_QUOTES, 'UTF-8') . '</button>';
    echo '</form>';
}

/**
 * WonderCMS hooks.
 */
$Wcms->addListener('css', 'olivaBannerAssets');
$Wcms->addListener('footer', 'olivaBannerRender');
$Wcms->addListener('settings', 'olivaBannerSettings');