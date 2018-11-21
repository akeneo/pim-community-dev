 'use strict';
/**
 * Family extension
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
        'oro/mediator',
        'pim/form',
        'pim/template/product/meta/family',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/i18n'
    ],
    function ($, _, mediator, BaseForm, template, FetcherRegistry, UserContext, i18n) {
        return BaseForm.extend({
            className: 'AknColumn-block',

            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);
                UserContext.off('change:catalogLocale change:catalogScope', this.render);
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                var familyPromise = _.isNull(this.getFormData().family) ?
                    $.Deferred().resolve(null) :
                    FetcherRegistry.getFetcher('family').fetch(this.getFormData().family);

                familyPromise.then(function (family) {
                    var product = this.getFormData();

                    this.$el.html(
                        this.template({
                            familyLabel: family ?
                                i18n.getLabel(
                                    family.labels,
                                    UserContext.get('catalogLocale'),
                                    product.family
                                ) : _.__('pim_common.none')
                        })
                    );

                    BaseForm.prototype.render.apply(this, arguments);
                }.bind(this));
            }
        });
    }
);
