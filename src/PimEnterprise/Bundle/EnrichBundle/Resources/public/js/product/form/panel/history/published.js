'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'text!pimee/template/product/panel/history/published'
    ],
    function (_, Backbone, BaseForm, publishedTemplate) {
        return BaseForm.extend({
            template: _.template(publishedTemplate),
            render: function () {
                var version = _.findWhere(this.getParent().versions, {published: true});
                if (version) {
                    this.getParent().$el.find('.product-version[data-version-id="' + version.id + '"] .version')
                        .append(this.template({
                            display: this.getParent()
                            .getParent()
                            .getParent()
                            .state.get('fullPanel') ? 'big' : 'small'
                        }));
                }

                return this;
            }
        });
    }
);
