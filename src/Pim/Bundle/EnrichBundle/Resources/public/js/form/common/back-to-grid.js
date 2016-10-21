'use strict';
/**
 * Back to grid extension
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
        'text!pim/template/form/back-to-grid',
        'routing',
        'pim/user-context',
        'oro/navigation'
    ],
    function (_, BaseForm, template, Routing, UserContext, Navigation) {
        return BaseForm.extend({
            tagName: 'a',
            className: 'AknTitleContainer-backLink back-link',
            template: _.template(template),
            config: {},
            attributes: {
                title: _.__('pim_enrich.navigation.link.back_to_grid')
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                UserContext.off('change:catalogLocale', this.render);
                this.listenTo(UserContext, 'change:catalogLocale', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template());
                this.$el.attr('href', Routing.generate(
                    this.config.backUrl,
                    {
                        dataLocale: UserContext.get('catalogLocale')
                    }
                ));

                Navigation.getInstance().processClicks(this.$el);

                return this;
            }
        });
    }
);
