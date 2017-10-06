/**
 * Page-specific Javascript file. Should generally be included as a separate asset bundle in your page template.
 * example: {{assets.js('js/pages/sign-in-or-register') | raw}}
 *
 * This script depends on widgets/expertises.js, uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /expertises
 */

$(function () {
    $('#widget-expertises').ufTable({
        dataUrl: site.uri.public + '/api/expertises'
    });

    // Bind creation button
    bindExpertiseCreationButton($('#widget-expertises'));

    // Bind table buttons
    $('#widget-expertises').on('pagerComplete.ufTable', function () {
        bindExpertiseButtons($(this));
    });
});
