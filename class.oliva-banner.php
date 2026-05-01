<?php

class OlivaBanner
{
    private $Wcms;
    private $translations = [];

    public function __construct($Wcms)
    {
        $this->Wcms = $Wcms;
    }

    private function loadTranslations()
    {
        $lang = $this->Wcms->get('config', 'adminLang');

        // Map WonderCMS adminLang → your file names
        $map = [
            'en' => 'en_US',
            'nl' => 'nl_NL',
            'es' => 'es_ES',
            'de' => 'de_DE',
            'fr' => 'fr_FR',
            'it' => 'it_IT'
        ];

        $langCode = $map[$lang] ?? 'en_US';

        $file = __DIR__ . '/languages/' . $langCode . '.ini';

        if (file_exists($file)) {
            $this->translations = parse_ini_file($file);
        } else {
            // Fallback
            $this->translations = parse_ini_file(__DIR__ . '/languages/en_US.ini');
        }
    }

    private function t($key)
    {
        return $this->translations[$key]
            ?? '[[' . $key . ']]'; // Helps debugging missing keys
    }

    // Ensure default values are populated in the database.js file
    public function populateDefaultValues() {
        global $Wcms;

        // Check and populate Banner Text
        $bannerText = $this->Wcms->get('config', 'olivaBannerBannerText');
        if (empty($bannerText) || is_object($bannerText)) {
            $this->Wcms->set('config', 'olivaBannerBannerText', $this->t('defaultBannerText'));
        }

        // Check and populate the close Text appearing on button
        $closeText = $this->Wcms->get('config', 'olivaBannerCloseText');
        if (empty($closeText) || is_object($closeText)) {
            $this->Wcms->set('config', 'olivaBannerCloseText', $this->t('defaultCloseText'));
        }

        // Check and populate the Text for the url to the information page of the banner content
        $moreText = $this->Wcms->get('config', 'olivaBannerMoreText');
        if (empty($moreText) || is_object($moreText)) {
            $this->Wcms->set('config', 'olivaBannerMoreText', $this->t('defaultMoreText'));
        }

        // Default page name to use on link from More Text
        $moreUrl = $this->Wcms->get('config', 'olivaBannerMoreUrl');
//        if (empty($moreUrl) || is_object($moreUrl)) {
//           $this->Wcms->set('config', 'olivaBannerMoreUrl', $this->t('defaultMoreUrl'));
//        }

        // Check and populate the expiry days on the cookie in use
        $bannerExpiry = $this->Wcms->get('config', 'olivaBannerExpiryDays');
        if (empty($bannerExpiry) || is_object($bannerExpiry)) {
            $this->Wcms->set('config', 'olivaBannerExpiryDays', $this->t('defaultBannerExpiry'));
        }

        // Default language values
        $defaultLanguage = 'NA'; // Default language
        if ($this->Wcms->get('config', 'siteLang') === 'en') {
            $defaultLanguage = 'en_US';
        }
        if ($this->Wcms->get('config', 'siteLang') === 'nl') {
            $defaultLanguage = 'nl_NL';
        }
        if ($this->Wcms->get('config', 'siteLang') === 'es') {
            $defaultLanguage = 'es_ES';
        }
        if ($this->Wcms->get('config', 'siteLang') === 'de') {
            $defaultLanguage = 'de_DE';
        }
        if ($this->Wcms->get('config', 'siteLang') === 'it') {
            $defaultLanguage = 'it_IT';
        }
        if ($this->Wcms->get('config', 'siteLang') === 'fr') {
            $defaultLanguage = 'fr_FR';
        }
        if ($defaultLanguage === 'NA') {
            $defaultLanguage = 'en_US';
        }

        // Check and populate olivaLanguage
        $language = $this->Wcms->get('config', 'olivaLanguage');
        if (empty($language) || is_object($language)) {
            $this->Wcms->set('config', 'olivaLanguage', $defaultLanguage);
        }
    }

    public function getBannerText() {
        return $this->Wcms->get('config', 'olivaBannerBannerText');
    }

    public function getCloseText() {
        return $this->Wcms->get('config', 'olivaBannerCloseText');
    }

    public function getMoreText() {
        return $this->Wcms->get('config', 'olivaBannerMoreText');
    }

    public function getMoreUrl() {
        return $this->Wcms->get('config', 'olivaBannerMoreUrl');
    }

    public function getExpiryDays() {
        return $this->Wcms->get('config', 'olivaBannerExpiryDays');
    }

    public function getLang() {
        return $this->Wcms->get('config', 'olivaLanguage');
    }

    public function alterAdmin(array $args): array
    {
        // Populate default values on plugin initialization
        $this->loadTranslations();
        $this->populateDefaultValues();

        $doc = new DOMDocument();
        @$doc->loadHTML(mb_convert_encoding($args[0], 'HTML-ENTITIES', 'UTF-8'));

        $currentPage = $doc->getElementById('currentPage');

        if (!$currentPage) {
            return $args;
        }

        $menuList = $currentPage
            ->parentNode
            ->parentNode
            ->childNodes
            ->item(1);

        $menuItem = $doc->createElement('li');
        $menuItem->setAttribute('class', 'nav-item');

        $menuItemA = $doc->createElement('a');
        $menuItemA->setAttribute('href', '#olivaSettings');
        $menuItemA->setAttribute('aria-controls', 'olivaSettings');
        $menuItemA->setAttribute('role', 'tab');
        $menuItemA->setAttribute('data-toggle', 'tab');
        $menuItemA->setAttribute('class', 'nav-link');
        $menuItemA->nodeValue = $this->t('OlivaSettings');

        $menuItem->appendChild($menuItemA);
        $menuList->appendChild($menuItem);

        $wrapper = $doc->createElement('div');
        $wrapper->setAttribute('role', 'tabpanel');
        $wrapper->setAttribute('class', 'tab-pane');
        $wrapper->setAttribute('id', 'olivaSettings');

        $form = $doc->createElement('form');
        $form->setAttribute('method', 'post');
        $form->setAttribute('action', ''); // important: post back to same admin page

        $title = $doc->createElement('h2');
        $title->nodeValue = $this->t('headingBannerSettings');
        $form->appendChild($title);

        // Banner text
        $label = $doc->createElement('label');
        $label->nodeValue = $this->t('labelBannerText');
        $form->appendChild($label);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'text');
        $input->setAttribute('name', 'oliva_banner_text');
        $input->setAttribute('class', 'form-control');
        $input->setAttribute('value', $this->getBannerText());
        $form->appendChild($input);

        // More information text
        $label = $doc->createElement('label');
        $label->nodeValue = $this->t('labelMoreText');
        $form->appendChild($label);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'text');
        $input->setAttribute('name', 'oliva_more_text');
        $input->setAttribute('class', 'form-control');
        $input->setAttribute('value', $this->getMoreText());
        $form->appendChild($input);

        // More information Url
        $label = $doc->createElement('label');
        $label->nodeValue = $this->t('labelMoreUrl');
        $form->appendChild($label);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'text');
        $input->setAttribute('name', 'oliva_more_url');
        $input->setAttribute('class', 'form-control');
        $input->setAttribute('value', $this->getMoreUrl());
        $form->appendChild($input);

        // Close text
        $label = $doc->createElement('label');
        $label->nodeValue = $this->t('labelCloseText');
        $form->appendChild($label);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'text');
        $input->setAttribute('name', 'oliva_close_text');
        $input->setAttribute('class', 'form-control');
        $input->setAttribute('value', $this->getCloseText());
        $form->appendChild($input);

        // Cookie expiry days
/* Disabled as I don't get it to work. See also oliva-banner.php        
        $label = $doc->createElement('label');
        $label->nodeValue = $this->t('labelBannerExpiry');
        $form->appendChild($label);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'text');
        $input->setAttribute('name', 'oliva_expiry_days');
        $input->setAttribute('class', 'form-control');
        $input->setAttribute('value', $this->getExpiryDays());
        $form->appendChild($input);
*/
        $saveButton = $doc->createElement('button');
        $saveButton->setAttribute('type', 'submit');
        $saveButton->setAttribute('name', 'saveOlivaSettings');
        $saveButton->nodeValue = $this->t('saveButton');
        $form->appendChild($saveButton);

        $title = $doc->createElement('h2');
        $title->nodeValue = $this->t('headingLanguageSettings');
        $form->appendChild($title);

        $label = $doc->createElement('label');
        $label->nodeValue = $this->t('labelVisitorLanguage');
        $form->appendChild($label);

        // Get all available language files
        $languagesDir = __DIR__ . '/languages';
        $languageFiles = glob($languagesDir . '/*.ini');
        $languageOptions = [];

        foreach ($languageFiles as $file) {
            $languageCode = basename($file, '.ini'); // Extract language code from file name
            $languageOptions[] = $languageCode; // Add to the list of available languages
        }

        // Set the current value
        $currentLang = $this->getLang();
        // Generate the language dropdown options
        $input = $doc->createElement('select');
        $input->setAttribute('name', 'oliva_language');
        $input->setAttribute('class', 'form-control');
        foreach ($languageOptions as $option) {
            $selectOption = $doc->createElement('option', $option);
            $selectOption->setAttribute('value', $option);
            if ($currentLang === $option) {
                $selectOption->setAttribute('selected', 'selected');
            }
            $input->appendChild($selectOption);
        }
        $form->appendChild($input);

        $saveButton = $doc->createElement('button');
        $saveButton->setAttribute('type', 'submit');
        $saveButton->setAttribute('name', 'saveOlivaSettings');
        $saveButton->nodeValue = $this->t('saveButton');
        $form->appendChild($saveButton);

        $wrapper->appendChild($form);

        $currentPage->parentNode->appendChild($wrapper);

        $args[0] = preg_replace(
            '~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i',
            '',
            $doc->saveHTML()
        );

        return $args;
    }

    // Save the values entered in the backend into database.js
    public function handleSettings(array $args): array
    {
//        file_put_contents(__DIR__ . '/debug-post.txt', print_r($_POST, true));

        // Bail out if the request is coming from the frontend
        if (!$this->Wcms->loggedIn) {
            return $args;
        }

        if (isset($_POST['saveOlivaSettings'])) {
            $bannerText = trim($_POST['oliva_banner_text'] ?? 'Welcome to our site');
            $moreText = trim($_POST['oliva_more_text'] ?? 'More Information');
            $closeText = trim($_POST['oliva_close_text'] ?? 'Close');
            $moreUrl = trim($_POST['oliva_more_url'] ?? 'bannerinfo');
            $bannerExpiry = trim($_POST['oliva_expiry_days'] ?? '180');
            $olivaLanguage = trim($_POST['oliva_language'] ?? 'en_US');

            $this->Wcms->set('config', 'olivaBannerBannerText', $bannerText);
            $this->Wcms->set('config', 'olivaBannerMoreText', $moreText);
            $this->Wcms->set('config', 'olivaBannerCloseText', $closeText);
            $this->Wcms->set('config', 'olivaBannerMoreUrl', $moreUrl);
            $this->Wcms->set('config', 'olivaBannerExpiryDays', $bannerExpiry);
            $this->Wcms->set('config', 'olivaLanguage', $olivaLanguage);
        }

        return $this->alterAdmin($args);
    }


    public function renderBanner(array $args): array
    {

        $bannerText = $this->getBannerText();
        $closeText  = $this->getCloseText();
        $moreText   = $this->getMoreText();
        $moreUrl    = $this->getMoreUrl();

        $linkHtml = '';

        if (!empty($moreUrl) && !empty($moreText)) {
            $linkHtml = '    <a class="oliva-banner-notice__link" href="' 
                . htmlspecialchars($moreUrl, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($moreText, ENT_QUOTES, 'UTF-8') . '</a>' . PHP_EOL;
        }

        $html = PHP_EOL . '<div id="oliva-banner-notice" class="oliva-banner-notice" role="dialog" aria-live="polite" aria-label="Cookie consent banner" hidden>' . PHP_EOL
            . '  <div class="oliva-banner-notice__text">' . htmlspecialchars($bannerText, ENT_QUOTES, 'UTF-8') . '</div>' . PHP_EOL
            . '  <div class="oliva-banner-notice__actions">' . PHP_EOL
            . $linkHtml
            . '    <button type="button" id="oliva-banner-accept" class="oliva-banner-notice__button">' . htmlspecialchars($closeText, ENT_QUOTES, 'UTF-8') . '</button>' . PHP_EOL
            . '  </div>' . PHP_EOL
            . '</div>' . PHP_EOL;

//        $args[0] = str_replace('</body>', $html . '</body>', $args[0]);
        $args[0] .= $html;

        return $args;
    }
}
