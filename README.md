# OlivaBanner for WonderCMS
By Steve Alink for Oliva Solutions

Simple WonderCMS plugin that shows a banner.

## Installation
1. Copy the folder `oliva-banner` to your WonderCMS `plugins` folder.
2. Make sure the path is: `plugins/oliva-banner/oliva-banner.php`.
3. Refresh your website.

## Language files
Translations are stored in:
plugins/OlivaCookies/languages/
Included files:
en_US.ini
nl_NL.ini
es_ES.ini

Each language file can contain:
noticeText = "Cookie notice text"
buttonText = "Accept"
moreText = "More information"
moreUrl = "/privacy"
ariaLabel = "Cookie notice"

The plugin reads the configured language from (as the standard HTML language value for visitors is not ISO coded: en_US is different from en_EN):
$Wcms->get('config', 'contactFormLanguage');
If this value is empty, invalid, or missing, it falls back to `en_US` and populates the config value.

## Important
This is a  plugin for a simple banner, not a full fledge bannering system with showing date ranges or something like that.

## Versions
1.0.1 29-Apr-2026 Initial version