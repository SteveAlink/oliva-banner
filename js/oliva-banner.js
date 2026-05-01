(function () {
    'use strict';

    var cookieName = 'oliva_banner_accepted';
    // Fallback if not set
    var days = typeof olivaBannerExpiryDays !== 'undefined' ? olivaBannerExpiryDays : 1;
    var cookieMaxAge = 60 * 60 * 24 * days; // 2 hours (change to * 24 * 2 for 2 days not seeing banner)

    function hasAcceptedBanner() {
        return document.cookie.split(';').some(function (item) {
            return item.trim().indexOf(cookieName + '=yes') === 0;
        });
    }

    function acceptBanner() {
        document.cookie = cookieName + '=yes; max-age=' + cookieMaxAge + '; path=/; SameSite=Lax';
        var notice = document.getElementById('oliva-banner-notice');
        if (notice) {
            notice.setAttribute('hidden', 'hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var notice = document.getElementById('oliva-banner-notice');
        var acceptButton = document.getElementById('oliva-banner-accept');

        if (!notice) {
            return;
        }

        if (!hasAcceptedBanner()) {
            notice.removeAttribute('hidden');
        }

        if (acceptButton) {
            acceptButton.addEventListener('click', acceptBanner);
        }
    });
}());
