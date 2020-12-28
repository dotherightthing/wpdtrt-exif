/**
 * @file DTRT EXIF frontend.js
 * @summary Front-end scripting for public pages.
 * @description PHP variables are provided in `wpdtrt_exif_config`.
 * @version 0.0.1
 * @since   0.7.0
 */

/* eslint-env browser */
/* global jQuery, wpdtrt_exif_config */
/* eslint-disable no-unused-vars */

/**
 * @namespace wpdtrtExifUi
 */
const wpdtrtExifUi = {

    /**
     * @summary Initialise front-end scripting
     * @since 0.0.1
     */
    init: () => {
        console.log('wpdtrtExifUi.init'); // eslint-disable-line no-console
    }
};

jQuery(document).ready(($) => {
    const config = wpdtrt_exif_config; // eslint-disable-line

    $.post(wpdtrt_exif_config.ajax_url, {
        action: 'wpdtrt_exif_data_refresh'
    }, (response) => {
        // console.log('Ajax complete');
    });

    wpdtrtExifUi.init();
});
