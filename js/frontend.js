/**
 * @file DTRT EXIF frontend.js
 * @summary
 *     Front-end scripting for public pages
 *     PHP variables are provided in `wpdtrt_exif_config`.
 * @version 0.0.1
 * @since   0.7.0 DTRT WordPress Plugin Boilerplate Generator
 */

/* eslint-env browser */
/* global document, jQuery, wpdtrt_exif_config */
/* eslint-disable no-unused-vars */

/**
 * @namespace wpdtrt_exif_ui
 */
const wpdtrt_exif_ui = {

    /**
     * Initialise front-end scripting
     * @since 0.0.1
     */
    init: () => {
        "use strict";

        console.log("wpdtrt_exif_ui.init");
    }
}

jQuery(document).ready( ($) => {

    "use strict";

    const config = wpdtrt_exif_config;

    $.post( wpdtrt_exif_config.ajax_url, {
        action: "wpdtrt_exif_data_refresh"
    }, ( response ) => {
        //console.log( 'Ajax complete' );
    });

    wpdtrt_exif_ui.init();
});
