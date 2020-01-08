'use strict';

define(
    [
        'underscore',
        'pim/form',
        'oro/mediator',
        'pim/common/grid',
        'oro/translator',
        'pim/user-context',
        'pim/common/form-modal-creator',
        'pim/template/family/tab/family-variant'
    ],
    function (
        _,
        BaseForm,
        mediator,
        Grid,
        __,
        UserContext,
        formModalCreator,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'tabbable variant',
            variantGrid: null,

            /**
             * @param {Object} meta
             */
            initialize: function (meta) {
                this.config = _.extend({}, meta.config);
                this.config.modelDependent = false;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.config.tabCode ? this.config.tabCode : this.code,
                    label: __(this.config.title)
                });

                this.listenTo(
                    this.getRoot(),
                    'pim_enrich.entity.family.family_variant.post_create',
                    (familyVariant) => {
                        mediator.trigger(`datagrid:doRefresh:${this.config.gridName}`);

                        formModalCreator.createModal(familyVariant.code, 'family-variant');
                    }
                );

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.variantGrid) {
                    this.variantGrid = new Grid(
                        this.config.gridName,
                        {
                            family_id: this.getFormData().meta.id,
                            localeCode: UserContext.get('catalogLocale')
                        }
                    );
                }

                this.$el.html(this.template());

                this.renderExtensions();
                this.getZone('grid').appendChild(this.variantGrid.render().el);
            }
        });
    }
);
