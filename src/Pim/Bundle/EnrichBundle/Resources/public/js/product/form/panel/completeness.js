'use strict';
/**
 * Completeness panel extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'text!pim/template/product/panel/completeness',
        'pim/fetcher-registry',
        'pim/i18n'
    ],
    function ($, _, BaseForm, template, FetcherRegistry, i18n) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-pane',
            events: {
                'click header': 'switchLocale',
                'click .missing-attributes span': 'showAttribute'
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('panel:register', {
                    code: this.code,
                    label: _.__('pim_enrich.form.product.panel.completeness.title')
                });

                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.update);
                this.listenTo(this.getRoot(), 'pim_enrich:form:change-family:after', this.onChangeFamily);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (this.code !== this.getParent().state.get('currentPanel')) {
                    return this;
                }

                if (this.getFormData().meta) {
                    $.when(
                        this.fetchCompleteness(),
                        FetcherRegistry.getFetcher('locale').fetchAll()
                    ).then(function (completeness, locales) {
                        this.$el.html(
                            this.template({
                                hasFamily: this.getFormData().family !== null,
                                completenesses: completeness.completenesses,
                                i18n: i18n,
                                locales: locales
                            })
                        );
                        this.delegateEvents();
                    }.bind(this));
                }

                return this;
            },

            /**
             * @returns {Promise}
             */
            fetchCompleteness: function () {
                return FetcherRegistry.getFetcher('product-completeness').fetchForProduct(
                    this.getFormData().meta.id,
                    this.getFormData().family
                );
            },

            /**
             * Toggle the current locale
             *
             * @param Event event
             */
            switchLocale: function (event) {
                var $completenessBlock = $(event.currentTarget).parents('.completeness-block');
                if ($completenessBlock.attr('data-closed') === 'false') {
                    $completenessBlock.attr('data-closed', 'true');
                } else {
                    $completenessBlock.attr('data-closed', 'false');
                }
            },

            /**
             * Set focus to the attribute given by the event
             *
             * @param Event event
             */
            showAttribute: function (event) {
                this.getRoot().trigger(
                    'pim_enrich:form:show_attribute',
                    {
                        attribute: event.currentTarget.dataset.attribute,
                        locale: event.currentTarget.dataset.locale,
                        scope: event.currentTarget.dataset.channel
                    }
                );
            },

            /**
             * Update the completeness by fetching it from the backend
             */
            update: function () {
                if (this.getFormData().meta) {
                    FetcherRegistry.getFetcher('completeness').clear(this.getFormData().meta.id);
                }

                this.render();
            },

            /**
             * On family change listener
             */
            onChangeFamily: function () {
                if (!_.isEmpty(this.getRoot().model._previousAttributes)) {
                    this.render();
                }
            }
        });
    }
);
