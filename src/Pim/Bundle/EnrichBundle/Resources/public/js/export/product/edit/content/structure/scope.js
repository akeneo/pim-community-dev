'use strict';
/**
 * Scope structure filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'oro/translator',
        'text!pim/template/export/product/edit/content/structure/scope',
        'pim/form',
        'pim/fetcher-registry',
        'pim/user-context',
        'jquery.select2'
    ],
    function (
        __,
        template,
        BaseForm,
        fetcherRegistry,
        UserContext
    ) {
        return BaseForm.extend({
            className: 'control-group',
            template: _.template(template),

            /**
             * Renders scopes dropdown.
             *
             * @return {Object}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                fetcherRegistry.getFetcher('channel').fetchAll().then(function (channels) {
                    if (!this.getScope()) {
                        this.setScope(_.first(channels).code);
                    }

                    this.$el.html(
                        this.template({
                            __: __,
                            locale: UserContext.get('uiLocale'),
                            channels: channels,
                            scope: this.getScope()
                        })
                    );

                    this.$('.select2').select2().on('change', this.updateState.bind(this));
                    this.$('[data-toggle="tooltip"]').tooltip();

                    this.renderExtensions();
                }.bind(this));

                return this;
            },

            /**
             * Sets new scope on field change.
             *
             * @param {Object} event
             */
            updateState: function(event) {
                this.setScope(event.target.value);
            },

            /**
             * Sets specified scope into root model.
             *
             * @param {String} code
             */
            setScope: function (code) {
                var data = this.getFormData();
                var before = data.structure.scope;

                data.structure.scope = code;
                this.setData(data);

                if (before !== code) {
                    this.getRoot().trigger('channel:update:after', data.structure.scope);
                }
            },

            /**
             * Gets scope from root model.
             *
             * @returns {String}
             */
            getScope: function () {
                var structure = this.getFormData().structure;

                if (_.isUndefined(structure)) {
                    return null;
                }

                return _.isUndefined(structure.scope) ? null : structure.scope;
            }
        });
    }
);
