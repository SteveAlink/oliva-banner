# OlivaBanner for WonderCMS
By Steve Alink for Oliva Solutions

Simple WonderCMS plugin that shows a banner.

## Installation
1. Copy the folder `oliva-banner` to your WonderCMS `plugins` folder.
2. Make sure the path is: `plugins/oliva-banner/oliva-banner.php`.
3. Refresh your website.
4. Create a new page bannerinfo, if you want to, that gives an extended explanation on the banner message

## Language files
Translations are stored in:
plugins/OlivaCookies/languages/
Included files:
de_DE.ini
en_US.ini
nl_NL.ini
es_ES.ini
fr_FR.ini
it_IT.ini

The language files are needed for the back and front end.
The language code value for admin, in the tab security, defines the language of the tab Oliva Settings
The visitors language code is used for the front end.
If this value is empty, invalid, or missing, it falls back to `en_US` and populates the config value.
A cookie, with the name oliva_banner_accepted, is saved. Its validity is set in js/oliva-banner.js and is valid for a day.
This results in showing the banner each day. This is especially handy if you have something new to mention every day.

## Important
This is a  plugin for a simple banner, not a full fledge bannering system with showing date ranges or something like that.
The banner is shown in the footer.

## Preview of Settings
In the settings is an additional tab shown. It looks, in English, like this.
<img width="991" height="672" alt="WcmsOlivaBannerPreviewSettings" src="https://github.com/user-attachments/assets/884a2d6f-fef5-48d1-b812-21bdf634b243" />

The language depents on the language for admin as set on the Security Tab.

## Versions
1.0.2 30-Apr-2026 More languages included. Backend reacts to language setting in Security tab
1.0.1 29-Apr-2026 Initial version
