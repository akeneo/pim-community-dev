'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/datagrid/model-action',
        'oro/loading-mask',
        'pim/form-builder',
        'pim/product-manager',
        'backbone/bootstrap-modal'
    ],
    function($, _, Backbone, ModelAction, LoadingMask, FormBuilder, ProductManager) {
        return ModelAction.extend({
            template: _.template(
                '<div id="product-edit-form"></div>'
            ),
            run: function() {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo($('#container'));
                loadingMask.show();

                $.when(
                    FormBuilder.build('pim/product-edit-form'),
                    ProductManager.get(this.model.get('id'))
                ).done(_.bind(function(form, product) {
                    var modal = new Backbone.BootstrapModal({
                        className: 'modal modal-large',
                        modalOptions: {
                            backdrop: 'static',
                            keyboard: false
                        },
                        content: this.template()
                    });
                    modal.open();

                    form.setData(product);
                    form.setElement('#product-edit-form').render();
                    form.$el.parent().before(
                        $('<i class="icon-remove"></i>').css({
                            'font-size': '30px',
                            'color': 'white',
                            'position': 'absolute',
                            'left': '-32px',
                            'top': '-30px',
                            'opacity': '0.8',
                            'cursor': 'pointer'
                        }).on('click', function () {
                            modal.close();
                        })
                    );

                    loadingMask.hide();
                    loadingMask.$el.remove();
                }, this));
            }
        });
    }
);
