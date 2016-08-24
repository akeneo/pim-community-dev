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
                this.getParent().getVersions()
                    .then(function (versions) {
                        var version = _.findWhere(versions, {published: true});
                        if (version) {
                            var $version = this.getParent().$el.find('.product-version[data-version-id="' + version.id + '"] .version');

                            if ($version.children('.label-published').length === 0) {
                                $version.append(this.template({
                                    display: this.getParent()
                                        .getParent()
                                        .getParent()
                                        .isFullPanel() ? 'big' : 'small'
                                }));
                            }
                        }
                    }.bind(this));

                return this;
            }
        });
    }
);
