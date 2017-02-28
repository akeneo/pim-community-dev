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
        'pim/user-context'
    ],
    function ($, _, BaseForm, template, FetcherRegistry, i18n, UserContext) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-pane completeness-panel',
            events: {
                'click header': 'switchLocale',
                'click .missing-attributes a': 'showAttribute'
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('panel:register', {
                    code: this.code,
                    label: _.__('pim_enrich.form.product.panel.completeness.title')
                });

                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:change-family:after', this.onChangeFamily);
                this.listenTo(UserContext, 'change:catalogLocale', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured || this.code !== this.getParent().getCurrentPanelCode()) {
                    return this;
                }

                if (this.getFormData().meta) {
                    $.when(
                        this.fetchCompleteness(),
                        FetcherRegistry.getFetcher('locale').fetchActivated()
                    ).then(function (completeness, locales) {
                        this.$el.html(
                            this.template({
                                hasFamily: this.getFormData().family !== null,
                                completenesses: this.sortCompleteness(completeness.completenesses),
                                i18n: i18n,
                                locales: locales,
                                catalogLocale: UserContext.get('catalogLocale')
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
             * Sort completenesses. Put the user current catalog locale first.
             *
             * @param completenesses
             *
             * @returns {Array}
             */
            sortCompleteness: function (completenesses) {
                if (_.isEmpty(completenesses)) {
                    return [];
                }
                var sortedCompleteness = [_.findWhere(completenesses, {locale: UserContext.get('catalogLocale')})];

                return _.union(sortedCompleteness, completenesses);
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
