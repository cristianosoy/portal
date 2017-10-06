/**
 * Page-specific Javascript file. Should generally be included as a separate asset bundle in your page template.
 * example: {{assets.js('js/pages/sign-in-or-register') | raw}}
 *
 * This script depends on widgets/countries.js, uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /countries
 */

$(function () {
    $('#widget-countries').ufTable({
        dataUrl: site.uri.public + '/api/countries'
    });

    // Bind creation button
    bindCountryCreationButton($('#widget-countries'));

    // Bind table buttons
    $('#widget-countries').on('pagerComplete.ufTable', function () {
        bindCountryButtons($(this));
    });
});
