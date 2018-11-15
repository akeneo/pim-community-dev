'use strict';
/**
 * Scope switcher extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/product/scope-switcher',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/i18n'
    ],
    function (
        _,
        __,
        BaseForm,
        template,
        FetcherRegistry,
        UserContext,
        i18n
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknDropdown AknButtonList-item scope-switcher',
            events: {
                'click li a': 'changeScope'
            },
            displayInline: false,
            displayLabel: true,
            config: {},

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                if (undefined !== config) {
                    this.config = config.config;
                }

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', function (localeEvent) {
                    if ('base_product' === localeEvent.context) {
                        UserContext.set('catalogLocale', localeEvent.localeCode);
                        this.render();
                    }
                }.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                FetcherRegistry.getFetcher('channel')
                    .fetchAll()
                    .then(function (channels) {
                        const params = {
                            scopeCode: channels[0].code,
                            context: this.config.context
                        };
                        this.getRoot().trigger('pim_enrich:form:scope_switcher:pre_render', params);

                        var scope = _.findWhere(channels, { code: params.scopeCode });

                        this.$el.html(
                            this.template({
                                channels: channels,
                                currentScope: i18n.getLabel(
                                    scope.labels,
                                    UserContext.get('catalogLocale'),
                                    scope.code
                                ),
                                catalogLocale: UserContext.get('catalogLocale'),
                                i18n: i18n,
                                displayInline: this.displayInline,
                                displayLabel: this.displayLabel,
                                label: __('pim_enrich.entity.channel.uppercase_label')
                            })
                        );

                        this.delegateEvents();
                    }.bind(this));

                return this;
            },

            /**
             * Set the current selected scope
             *
             * @param {Event} event
             */
            changeScope: function (event) {
                this.getRoot().trigger('pim_enrich:form:scope_switcher:change', {
                    scopeCode: event.currentTarget.dataset.scope,
                    context: this.config.context
                });

                this.render();
            },

            /**
             * Updates the inline display value
             *
             * @param {Boolean} value
             */
            setDisplayInline: function (value) {
                this.displayInline = value;
            },

            /**
             * Updates the display label value
             *
             * @param {Boolean} value
             */
            setDisplayLabel: function (value) {
                this.displayLabel = value;
            }
        });
    }
);
