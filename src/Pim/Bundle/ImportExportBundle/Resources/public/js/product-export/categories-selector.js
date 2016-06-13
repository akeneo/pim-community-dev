'use strict';
/**
 * Categories selector field
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/translator',
        'pim/product-export/categories-selector-tree',
        'text!pim/template/product-export/categories-selector'
    ],
    function ($, _, Backbone, __, CategoryTree, template) {

        var TreeModal = Backbone.BootstrapModal.extend({
            className: 'modal jstree-modal'
        });

        return Backbone.View.extend({

            template: _.template(template),

            events: {
                'click button.btn.edit': 'open'
            },

            attributes: {
                disabled: false
            },

            /**
             * Overrides the constructor in order to enable data binding between model and view
             */
            initialize: function () {
                this.model.bind('change:included', function () {
                    $('#' + this.$el.data('included-input')).val(this.model.get('included').join());
                    this.render();
                }.bind(this));

                this.model.bind('change:excluded', function () {
                    $('#' + this.$el.data('excluded-input')).val(this.model.get('excluded').join());
                    this.render();
                }.bind(this));
            },

            /**
             * Opens the modal then instantiates the creation form inside it.
             * This function returns a rejected promise when the popin
             * is canceled and a resolved one when it's validated.
             *
             * @return {object} A promise
             */
            open: function (e) {

                e.preventDefault();

                var modal = new TreeModal({
                    title: __('pim_connector.export.categories.selector.modal.title'),
                    cancelText: __('pim_connector.export.categories.selector.modal.cancel'),
                    okText: __('pim_connector.export.categories.selector.modal.confirm'),
                    content: ''
                });

                modal.render();

                var tree = new CategoryTree({
                    el: modal.$el.find('.modal-body'),
                    attributes: {
                        'channel': $('#' + this.$el.data('channel-input')).val()
                    },
                    model: this.model.clone()
                });

                tree.render();
                modal.open();

                modal.on('cancel', function () {
                    modal.remove();
                    tree.remove();
                });

                modal.on('ok', function () {
                    this.model.set('included', tree.model.get('included'));
                    this.model.set('excluded', tree.model.get('excluded'));

                    modal.close();
                    modal.remove();
                    tree.remove();
                }.bind(this));
            },

            /**
             * Render the field html into the View element
             */
            render: function () {
                this.$el.html(this.template({
                    labelEdit: __('pim_connector.export.categories.selector.edit'),
                    labelInfo: __(
                        'pim_connector.export.categories.selector.label',
                        {count: this.model.get('included').length},
                        this.model.get('included').length
                    ),
                    disabled: this.attributes.disabled
                }));
            }
        });
    }
);
