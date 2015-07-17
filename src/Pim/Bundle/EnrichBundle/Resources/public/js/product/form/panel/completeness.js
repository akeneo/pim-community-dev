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
        'pim/i18n',
        'oro/mediator'
    ],
    function ($, _, BaseForm, template, FetcherRegistry, i18n, mediator) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-pane',
            code: 'completeness',
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

                mediator.on('product:action:post_update', _.bind(this.update, this));

                this.listenTo(this.getRoot().model, 'change:family', this.onChangeFamily);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (this.getRoot().model.get('meta')) {
                    $.when(
                        FetcherRegistry.getFetcher('completeness').fetchForProduct(this.getRoot().model.get('meta').id),
                        FetcherRegistry.getFetcher('locale').fetchAll()
                    ).done(_.bind(function (completenesses, locales) {
                        this.$el.html(
                            this.template({
                                hasFamily: this.getRoot().model.get('family') !== null,
                                completenesses: completenesses,
                                i18n: i18n,
                                locales: locales
                            })
                        );
                        this.delegateEvents();
                    }, this));
                }

                return this;
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
                mediator.trigger(
                    'show_attribute',
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
                if (this.getRoot().model.get('meta')) {
                    FetcherRegistry.getFetcher('completeness').clear(this.getRoot().model.get('meta').id);
                }

                this.render();
            },

            /**
             * On family change listener
             *
             * @param Model model the current product model
             */
            onChangeFamily: function (model) {
                if (!_.isEmpty(model._previousAttributes)) {
                    this.render();
                }
            }
        });
    }
);
