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
        'oro/translator',
        'pim/form',
        'pim/template/product/completeness',
        'pim/fetcher-registry',
        'pim/i18n',
        'pim/user-context'
    ],
    function ($, _, __, BaseForm, template, FetcherRegistry, i18n, UserContext) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-pane completeness-panel',
            initialFamily: null,
            events: {
                'click header': 'switchLocale',
                'click .missing-attributes a': 'showAttribute'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.initialFamily = null;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: __('pim_enrich.form.product.panel.completeness.title')
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
                if (!this.configured || this.code !== this.getParent().getCurrentTab()) {
                    return this;
                }

                if (this.getFormData().meta) {
                    $.when(
                        FetcherRegistry.getFetcher('channel').fetchAll(),
                        FetcherRegistry.getFetcher('locale').fetchActivated()
                    ).then(function (channels, locales) {
                        if (null === this.initialFamily) {
                            this.initialFamily = this.getFormData().family;
                        }

                        this.$el.html(
                            this.template({
                                hasFamily: this.getFormData().family !== null,
                                completenesses: this.sortCompleteness(this.getFormData().meta.completenesses),
                                i18n: i18n,
                                channels: channels,
                                locales: locales,
                                uiLocale: UserContext.get('uiLocale'),
                                catalogLocale: UserContext.get('catalogLocale'),
                                hasFamilyChanged: this.getFormData().family !== this.initialFamily
                            })
                        );
                        this.delegateEvents();
                    }.bind(this));
                }

                return this;
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
                    var data = this.getFormData();
                    data.meta.completenesses = [];
                    this.setData(data);

                    this.render();
                }
            }
        });
    }
);
