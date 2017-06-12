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
        'pim/form',
        'pim/template/product/scope-switcher',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/i18n'
    ],
    function (_, BaseForm, template, FetcherRegistry, UserContext, i18n) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknDropdown AknButtonList-item scope-switcher',
            events: {
                'click li a': 'changeScope'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                FetcherRegistry.getFetcher('channel')
                    .fetchAll()
                    .then(function (channels) {
                        var params = { scopeCode: channels[0].code };
                        this.trigger('pim_enrich:form:scope_switcher:pre_render', params);

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
                                i18n: i18n
                            })
                        );

                        this.delegateEvents();
                    }.bind(this)
                );

                return this;
            },

            /**
             * Set the current selected scope
             *
             * @param {Event} event
             */
            changeScope: function (event) {
                this.trigger('pim_enrich:form:scope_switcher:change', {
                    scopeCode: event.currentTarget.dataset.scope
                });

                this.render();
            }
        });
    }
);
