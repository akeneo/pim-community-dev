'use strict';

define(
    [
        'underscore',
        'jquery',
        'backbone',
        'routing',
        'pim/form',
        'pim/field-manager',
        'pim/entity-manager',
        'oro/mediator',
        'oro/messenger',
        'pim/user-context',
        'text!pim/template/product/tab/attribute/add-option',
        'text!pim/template/product/tab/attribute/add-option-error',
        'text!pim/template/product/tab/attribute/add-option-modal'
    ],
    function (
        _,
        $,
        Backbone,
        Routing,
        BaseForm,
        FieldManager,
        EntityManager,
        mediator,
        messenger,
        userContext,
        addOptionTemplate,
        addOptionErrorTemplate,
        addOptionModalTemplate
    ) {
        return BaseForm.extend({
            addOptionTemplate: _.template(addOptionTemplate),
            modalOptionAdderTemplate: _.template(addOptionModalTemplate),
            errorTemplate: _.template(addOptionErrorTemplate),
            initialize: function () {
                _.bindAll(this, 'renderModal');

                return BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                mediator.on('field:extension:add', _.bind(this.addExtension, this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            addExtension: function (event) {
                var field = event.field;
                var $element = this.addOptionTemplate({
                    attributeId: event.field.id
                });
                if (
                    event.field.attribute.type === 'pim_catalog_simpleselect' ||
                    event.field.attribute.type === 'pim_catalog_multiselect'
                ){
                    field.addElement('footer', 'add_option', $element);
                    field.$el.off('click', '.add-attribute-option', this.renderModal);
                    field.$el.on('click', '.add-attribute-option', this.renderModal);
                }

                return this;
            },
            renderModal: function (event) {
                var modal = new Backbone.BootstrapModal({
                    modalOptions: {
                        backdrop: 'static',
                        keyboard: false
                    },
                    allowCancel: true,
                    okCloses: false,
                    cancelText: _.__('pim_enrich.form.product.tab.attributes.add_option.cancel'),
                    title: _.__('pim_enrich.form.product.tab.attributes.add_option.title'),
                    content: this.modalOptionAdderTemplate(),
                    okText: _.__('pim_enrich.form.product.tab.attributes.add_option.add')
                });

                modal.open();

                modal.on('cancel');
                modal.on('ok', _.bind(function () {
                    var locale = userContext.get('catalogLocale');
                    var value = $('.add-option-input.option-value').val();
                    var code = $('.add-option-input.option-code').val();
                    var optionValues = {};
                    optionValues[locale] = {
                        locale: locale,
                        value: value
                    };
                    $.ajax({
                        method: 'POST',
                        url: Routing.generate(
                            'pim_enrich_attributeoption_create',
                            { attributeId: event.currentTarget.dataset.attributeId }
                        ),
                        data: JSON.stringify({
                            code: code,
                            optionValues: optionValues
                        }),
                    }).done(function(response) {
                        modal.close();
                    }).fail(_.bind(function(response) {
                        var field = $('.modal .control-group.option-code');
                        var $element = this.errorTemplate({errors: response.responseJSON.children.code.errors});
                        field.append($element);
                    }, this));
                }, this));

                return this;
            }
        });
    }
);
