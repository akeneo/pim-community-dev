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
        'backbone',
        'pim/form',
        'pim/fetcher-registry',
        'pim/product-manager',
        'text!pim/template/product/meta/change-family-modal',
        'pim/user-context',
        'backbone/bootstrap-modal',
        'jquery.select2'
    ],
    function (_, Backbone, BaseForm, FetcherRegistry, ProductManager, modalTemplate, UserContext) {
        var FormView = BaseForm.extend({
            tagName: 'i',
            className: 'icon-pencil change-family',
            modalTemplate: _.template(modalTemplate),
            events: {
                'click': 'showModal'
            },
            render: function () {
                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },
            showModal: function () {
                FetcherRegistry.getFetcher('family').fetchAll().done(function (families) {
                    var familyModal = new Backbone.BootstrapModal({
                        allowCancel: true,
                        title: _.__('pim_enrich.form.product.change_family.modal.title'),
                        content: this.modalTemplate({
                            families: families,
                            product:  this.getFormData(),
                            locale:   UserContext.get('catalogLocale')
                        })
                    });

                    familyModal.on('ok', function () {
                        var selectedFamily = familyModal.$('select').select2('val') || null;

                        this.getFormModel().set('family', selectedFamily);
                        ProductManager.generateMissing(this.getFormData()).then(function (product) {
                            this.getRoot().trigger('pim_enrich:form:change-family:before');

                            this.setData(product);

                            this.getRoot().trigger('pim_enrich:form:change-family:after');
                            familyModal.close();
                        }.bind(this));
                    }.bind(this));

                    familyModal.open();
                    familyModal.$('select').select2({ allowClear: true });
                    familyModal.$('.modal-body').css({'line-height': '25px', 'height': 130});
                }.bind(this));
            }
        });

        return FormView;
    }
);
