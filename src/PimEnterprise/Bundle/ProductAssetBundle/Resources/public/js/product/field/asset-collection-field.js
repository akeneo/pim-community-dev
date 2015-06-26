'use strict';
/**
 * Asset collection field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
define(
    [
        'pim/field',
        'underscore',
        'backbone',
        'text!pam/template/product/field/asset-collection',
        'pim/fetcher-registry',
        'routing',
        'pim/form-builder'
    ],
    function (
        Field,
        _,
        Backbone,
        fieldTemplate,
        FetcherRegistry,
        Routing,
        FormBuilder
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            events: {
                'click .add-asset': 'updateAssets'
            },
            getTemplateContext: function () {
                return $.when(
                    Field.prototype.getTemplateContext.apply(this, arguments),
                    FetcherRegistry.getFetcher('asset').findByIdentifiers(this.getCurrentValue().value)
                ).then(_.bind(function (templateContext, assets) {
                    templateContext.assets = _.map(this.getCurrentValue().value, function (assetCode) {
                        return _.findWhere(assets, {code: assetCode});
                    });
                    templateContext.Routing = Routing;
                    console.log(templateContext);
                    return templateContext;
                }, this));
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            updateModel: function () {

            },
            setFocus: function () {

            },
            updateAssets: function () {
                this.manageAssets().done(_.bind(function (assets) {
                    this.setCurrentValue(assets);
                    this.render();
                }, this));
            },
            manageAssets: function () {
                var deferred = $.Deferred();

                FormBuilder.build('pam/picker/asset-grid').done(_.bind(function (form) {
                    var modal = new Backbone.BootstrapModal({
                        modalOptions: {
                            backdrop: 'static',
                            keyboard: false
                        },
                        allowCancel: true,
                        okCloses: false,
                        title: _.__('pim_enrich.form.attribute_option.add_option_modal.title'),
                        content: '',
                        cancelText: _.__('pim_enrich.form.attribute_option.add_option_modal.cancel'),
                        okText: _.__('pim_enrich.form.attribute_option.add_option_modal.confirm')
                    });

                    modal.open();
                    modal.$el.addClass('modal-asset');
                    form.setElement(modal.$('.modal-body'))
                        .render()
                        .setAssets(this.getCurrentValue().value);

                    modal.on('cancel', deferred.reject);
                    modal.on('ok', _.bind(function () {
                        modal.close();

                        console.log(form.getAssets());

                        deferred.resolve(form.getAssets());
                    }, this));
                }, this));


                return deferred.promise();
            }
        });
    }
);
