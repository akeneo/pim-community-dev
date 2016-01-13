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
        'text!pim/template/product/back-to-grid',
        'routing',
        'pim/user-context',
        'oro/navigation'
    ],
    function (_, BaseForm, template, Routing, UserContext, Navigation) {
        return BaseForm.extend({
            className: 'btn-group',
            template: _.template(template),

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
                this.$el.html(this.template({
                    path: Routing.generate(
                        'pimee_workflow_published_product_index',
                        {
                            dataLocale: UserContext.get('catalogLocale')
                        }
                    )
                }));

                Navigation.getInstance().processClicks(this.$('a'));

                return this;
            }
        });
    }
);
