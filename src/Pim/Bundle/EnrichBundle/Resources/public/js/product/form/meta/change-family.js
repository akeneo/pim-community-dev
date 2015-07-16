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
        'oro/mediator',
        'backbone/bootstrap-modal',
        'jquery.select2'
    ],
    function (_, Backbone, BaseForm, FetcherRegistry, ProductManager, modalTemplate, UserContext, mediator) {
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
                FetcherRegistry.getFetcher('family').fetchAll().done(_.bind(function (families) {
                    var familyModal = new Backbone.BootstrapModal({
                        allowCancel: true,
                        title: _.__('pim_enrich.form.product.change_family.modal.title'),
                        content: this.modalTemplate({
                            families: families,
                            product:  this.getData(),
                            locale:   UserContext.get('catalogLocale')
                        })
                    });

                    familyModal.on('ok', _.bind(function () {
                        var selectedFamily = familyModal.$('select').select2('val') || null;

                        this.getRoot().model.set('family', selectedFamily);
                        ProductManager.generateMissing(this.getRoot().model.attributes).then(_.bind(function (product) {
                            this.setData(product);

                            mediator.trigger('change-family:change:after');
                            familyModal.close();
                        }, this));

                    }, this));

                    familyModal.open();
                    familyModal.$('select').select2({ allowClear: true });
                    familyModal.$('.modal-body').css({'line-height': '25px', 'height': 130});
                }, this));
            }
        });

        return FormView;
    }
);
