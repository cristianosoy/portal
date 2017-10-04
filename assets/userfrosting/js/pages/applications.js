/**
 * Page-specific Javascript file. Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on widgets/applications.js, uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /applications
 */

$(document).ready(function () {
    $('#widget-applications').ufTable({
        dataUrl: site.uri.public + '/api/applications'
    });

    // Bind table buttons
    $('#widget-applications').on('pagerComplete.ufTable', function () {
        bindApplicationButtons($(this));
    });
});
