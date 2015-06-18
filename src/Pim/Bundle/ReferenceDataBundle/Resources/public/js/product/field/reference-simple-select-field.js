'use strict';

define(
    [
        'underscore',
        'pim/simple-select-field',
        'routing',
        'pim/entity-manager'
    ],
    function (_, SimpleselectField, Routing, EntityManager) {
        return SimpleselectField.extend({
            fieldType: 'reference-simple-select',
            getTemplateContext: function () {
                return SimpleselectField.prototype.getTemplateContext.apply(this, arguments)
                    .then(function (templateContext) {
                        templateContext.userCanAddOption = false;

                        return templateContext;
                    });
            },
            getChoiceUrl: function () {
                return EntityManager.getRepository('referenceDataConfiguration').findAll()
                    .then(_.bind(function (config) {
                        return Routing.generate(
                            'pim_ui_ajaxentity_list',
                            {
                                'class': config[this.attribute.reference_data_name].class,
                                'dataLocale': this.context.locale,
                                'collectionId': this.attribute.id,
                                'options': {'type': 'code'}
                            }
                        );
                    }, this));
            }
        });
    }
);
