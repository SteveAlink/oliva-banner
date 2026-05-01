<?php
/**
 * oliva-banner - simple banner plugin for WonderCMS.
 * Prepared by Steve Alink for Oliva Solutions
 *
 * Shows a banner with information until the visitor accepts it.
 * Place this folder in: /plugins/oliva-banner/
 */

if (!defined('VERSION')) {
    die('Direct access is not allowed.');
}

global $Wcms;

require_once 'class.oliva-banner.php';

$olivaBanner = new OlivaBanner($Wcms);


/**
 * Register listeners for WonderCMS 3.x and older variants where possible.
 */
if (isset($Wcms) && is_object($Wcms)) {
    $Wcms->addListener('css', 'olivaBannerCss');
    $Wcms->addListener('js', 'olivaBannerJs');
    $Wcms->addListener('settings', [$olivaBanner, 'handleSettings']);
    $Wcms->addListener('footer', [$olivaBanner, 'renderBanner']);
} elseif (class_exists('wCMS')) {
    wCMS::addListener('css', 'olivaBannerCss');
    wCMS::addListener('js', 'olivaBannerJs');
    wCMS::addListener('settings', [$olivaBanner, 'handleSettings']);
    wCMS::addListener('footer', [$olivaBanner, 'renderBanner']);
}

function olivaBannerPluginBasePath()
{
    return 'plugins/oliva-banner/';
}

function olivaBannerCss($args)
{
    $css = '<link rel="stylesheet" href="' . olivaBannerPluginBasePath() . 'css/style.css" type="text/css">';

    if (isset($args[0]) && is_array($args[0])) {
        $args[0][] = $css;
    } else {
        $args[0] = ($args[0] ?? '') . $css;
    }

    return $args;
}

function olivaBannerJs($args)
{
    // Tried to obtain then number of days that the cookie is active dynamically. Didn't get it to work.
    //$days = $olivaBanner->getExpiryDays();
    $days = 1;
    $js = '<script>
        console.log("Oliva expiry days from PHP:", ' . json_encode($days) . ');
        var olivaBannerExpiryDays = ' . $days . ';
    </script>';

    $js .= '<script src="' . olivaBannerPluginBasePath() . 'js/oliva-banner.js" defer></script>';

    if (isset($args[0]) && is_array($args[0])) {
        $args[0][] = $js;
    } else {
        $args[0] = ($args[0] ?? '') . $js;
    }

    return $args;
}
