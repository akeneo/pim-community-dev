'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/datagrid/model-action',
        'oro/loading-mask',
        'pim/form-builder',
        'pim/product-manager'
    ],
    function($, _, ModelAction, LoadingMask, FormBuilder, ProductManager) {
        return ModelAction.extend({
            template: _.template(
                '<div id="product-edit-form"></div>'
            ),
            initialize: function () {
                _.bindAll(this, 'close', 'onEscape');

                return ModelAction.prototype.initialize.apply(this, arguments);
            },
            run: function() {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo($('#container'));
                loadingMask.show();

                $.when(
                    FormBuilder.build('pim/product-edit-form'),
                    ProductManager.get(this.model.get('id'))
                ).done(_.bind(function(form, product) {
                    this.$el.html(this.template())
                        .appendTo('body')
                        .css({
                            position:   'fixed',
                            top:        '65px',
                            left:       0,
                            bottom:     0,
                            right:      0,
                            background: 'white',
                            'z-index':  999
                        });

                    form.setData(product);
                    form.setElement('#product-edit-form').render();
                    form.$el.parent().prepend(
                        $('<i class="icon-remove"></i>').css({
                            'font-size': '22px',
                            color:       '#999',
                            position:    'absolute',
                            left:        '2px',
                            top:         '2px',
                            cursor:      'pointer',
                            'z-index':   999
                        }).on('click', this.close)
                    );

                    $(document).on('keyup', this.onEscape);

                    loadingMask.hide();
                    loadingMask.$el.remove();
                }, this));
            },

            onEscape: function (e) {
                if (27 === e.which) {
                    this.close();
                }
            },

            close: function () {
                $(document).off('keyup', this.onEscape);
                this.$el.remove();
            }
        });
    }
);
