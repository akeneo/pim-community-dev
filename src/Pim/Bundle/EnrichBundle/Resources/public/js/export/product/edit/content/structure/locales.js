'use strict';
/**
 * Locale structure filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'text!pim/template/export/product/edit/content/structure/locales',
        'pim/form',
        'pim/fetcher-registry',
        'pim/initselect2'
    ],
    function (
        template,
        BaseForm,
        fetcherRegistry
    ) {
        return BaseForm.extend({
            template: _.template(template),
            configure: function () {
                this.listenTo(this.getRoot(), 'channel:update:after', this.channelUpdated.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                var defaultLocalesPromise = (new $.Deferred()).resolve();
                if (_.isEmpty(this.getLocales())) {
                    defaultLocalesPromise = this.setDefaultLocales();
                }

                $.when(
                    fetcherRegistry.getFetcher('channel').fetch(this.getFormData().structure.scope),
                    defaultLocalesPromise
                ).then(function (scope) {
                    this.$el.html(
                        this.template({
                            locales: this.getLocales(),
                            availableLocales: scope.locales
                        })
                    );

                    this.$('.select2').select2().on('change', this.updateModel.bind(this));

                    this.renderExtensions();
                }.bind(this));

                return this;
            },
            updateModel: function (event) {
                this.setLocales($(event.target).val());
            },
            setLocales: function (codes) {
                var data = this.getFormData();
                var before = data.structure.locales;

                data.structure.locales = codes;
                this.setData(data);

                if (before !== codes) {
                    this.getRoot().trigger('locales:update:after', codes);
                }
            },
            getLocales: function () {
                return this.getFormData().structure.locales;
            },
            channelUpdated: function () {
                this.setDefaultLocales()
                    .then(function () {
                        this.render();
                    }.bind(this));
            },
            setDefaultLocales: function () {
                return fetcherRegistry.getFetcher('channel')
                    .fetch(this.getFormData().structure.scope)
                    .then(function (scope) {
                        this.setLocales(scope.locales);

                        return;
                    }.bind(this));
            }
        });
    }
);
