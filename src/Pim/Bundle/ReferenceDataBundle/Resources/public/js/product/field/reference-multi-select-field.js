'use strict';

define(
    [
        'underscore',
        'pim/multi-select-field',
        'routing',
        'pim/entity-manager'
    ],
    function (_, MultiselectField, Routing, EntityManager) {
        return MultiselectField.extend({
            fieldType: 'reference-multi-select',
            getTemplateContext: function () {
                return MultiselectField.prototype.getTemplateContext.apply(this, arguments)
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
