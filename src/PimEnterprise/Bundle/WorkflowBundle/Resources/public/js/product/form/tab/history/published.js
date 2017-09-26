'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'pimee/template/product/tab/history/published'
    ],
    function (_, Backbone, BaseForm, publishedTemplate) {
        return BaseForm.extend({
            template: _.template(publishedTemplate),
            render: function () {
                this.getParent().getVersions()
                    .then(function (versions) {
                        var version = _.findWhere(versions, {published: true});
                        if (version) {
                            var $version = this.getParent().$el.find(
                                '.entity-version[data-version-id="' + version.id + '"] .actions'
                            );

                            if ($version.children('.label-published').length === 0) {
                                $version.prepend(this.template());
                            }
                        }
                    }.bind(this));

                return this;
            }
        });
    }
);
