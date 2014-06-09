define(['jquery', 'underscore', 'oro/translator', 'oro/app', 'oro/datafilter/text-filter', 'routing', 'pim/initselect2', 'jquery.select2'],
function($, _, __, app, TextFilter, Routing, initSelect2) {
    'use strict';

    return TextFilter.extend({
        choiceUrl: null,
        choiceUrlParams: {},

        popupCriteriaTemplate: _.template(
            '<div>' +
                '<div class="btn-group">' +
                    '<input type="text" name="value" value="" data-multiple="true" data-min-input-length="1" />' +
                '</div>' +
                '<div class="btn-group">' +
                    '<button type="button" class="btn btn-primary filter-update"><%- _.__("Update") %></button>' +
                '</div>' +
            '</div>'
        ),

        initialize: function(options) {
            options = options || {};
            if (_.has(options, 'choiceUrl')) {
                this.choiceUrl = options.choiceUrl;
            }
            if (_.has(options, 'choiceUrlParams')) {
                this.choiceUrlParams = options.choiceUrlParams;
            }

            TextFilter.prototype.initialize.apply(this, arguments);
        },

        _renderCriteria: function() {
            TextFilter.prototype._renderCriteria.apply(this, arguments);

            var $select = this.$(this.criteriaValueSelectors.value);
            $select.attr({ 'data-url': Routing.generate(this.choiceUrl, this.choiceUrlParams) }).css('width', '290px');

            initSelect2.initSelect($select);

            return this;
        }
    });
});
