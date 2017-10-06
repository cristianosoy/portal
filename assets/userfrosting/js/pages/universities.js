/**
 * Page-specific Javascript file. Should generally be included as a separate asset bundle in your page template.
 * example: {{assets.js('js/pages/sign-in-or-register') | raw}}
 *
 * This script depends on widgets/universities.js, uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /universities
 */

$(function () {
    $('#widget-universities').ufTable({
        dataUrl: site.uri.public + '/api/universities'
    });

    // Bind creation button
    bindUniversityCreationButton($('#widget-universities'));

    // Bind table buttons
    $('#widget-universities').on('pagerComplete.ufTable', function () {
        bindUniversityButtons($(this));
    });
});
