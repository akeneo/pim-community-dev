'use strict';

/**
 * Back to grid extension
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
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
            attributes: {
                title: _.__('pim_enrich.navigation.link.back_to_grid')
            },

            /**
             * @inheritdoc
             */
            configure: function () {
                UserContext.off('change:catalogLocale', this.render);
                this.listenTo(UserContext, 'change:catalogLocale', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * @inheritdoc
             */
            render: function () {
                this.$el.html(this.template());
                this.$el.attr('href', Routing.generate(
                    'pimee_workflow_published_product_index',
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
