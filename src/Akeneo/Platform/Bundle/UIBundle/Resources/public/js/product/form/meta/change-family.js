'use strict';
/**
 * Change family extension
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
        'backbone',
        'pim/form',
        'pim/template/product/meta/change-family-modal',
        'pim/common/select2/family',
        'pim/initselect2',
        'bootstrap-modal',
        'jquery.select2'
    ],
    function (
        _,
        __,
        Backbone,
        BaseForm,
        modalTemplate,
        Select2Configurator,
        initSelect2
    ) {
        return BaseForm.extend({
            className: 'AknColumn-blockDown change-family',
            modalTemplate: _.template(modalTemplate),
            events: {
                'click': 'showModal'
            },
            render: function () {
                if (null !== this.getFormData().meta.family_variant) {
                    this.$el.remove();

                    return;
                }

                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },
            showModal: function () {
                var familyModal = new Backbone.BootstrapModal({
                    className: 'modal modal--fullPage',
                    allowCancel: true,
                    content: this.modalTemplate({
                        product: this.getFormData(),
                        title: __('pim_enrich.entity.product.module.change_family.title'),
                        subtitle: __('pim_enrich.entity.product.uppercase_label'),
                        placeholder: __('pim_enrich.entity.product.module.change_family.empty_selection'),
                        mergeAttributes: __('pim_enrich.entity.product.module.change_family.merge_attributes'),
                        keepAttributes: __('pim_enrich.entity.product.module.change_family.keep_attributes'),
                        ok: __('pim_common.ok'),
                    })
                });

                familyModal.open();

                familyModal.$el.find('.modal-footer .ok').remove();
                familyModal.$el.find('.ok').click(() => {
                    var selectedFamily = familyModal.$('.family-select2').select2('val') || null;

                    this.getRoot().trigger('pim_enrich:form:change-family:before');

                    this.setData({ family: selectedFamily });
                    familyModal.close();

                    this.getRoot().trigger('pim_enrich:form:change-family:after');
                });

                var options = Select2Configurator.getConfig(this.getFormData().family);

                initSelect2.init(familyModal.$('.family-select2'), options).select2('val', []);
            }
        });
    }
);
