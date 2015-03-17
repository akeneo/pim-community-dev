"use strict";

define([
        'pim/config-manager',
        'pim/boolean-field',
        'pim/date-field',
        'pim/media-field',
        'pim/metric-field',
        'pim/multi-select-field',
        'pim/number-field',
        'pim/price-collection-field',
        'pim/simple-select-field',
        'pim/text-field',
        'pim/textarea-field'
    ],
    function (
        ConfigManager,
        BooleanField,
        DateField,
        MediaField,
        MetricField,
        MultiSelectField,
        NumberField,
        PriceCollectionField,
        SimpleSelectField,
        TextField,
        TextareaField
    ) {
    return {
        fields: {},
        getField: function (attributeCode) {
            var promise = $.Deferred();

            if (this.fields[attributeCode]) {
                promise.resolve(this.fields[attributeCode]);

                return promise.promise();
            }

            ConfigManager.getEntity('attributes', attributeCode).done(_.bind(function(attribute) {
                var field = this.getFieldForAttribute(attribute);

                this.fields[attributeCode] = field;
                promise.resolve(this.fields[attributeCode]);
            }, this));

            return promise.promise();
        },
        getFields: function() {
            return this.fields;
        },
        getProductAttributeGroups: function()
        {
            _.each(this.fields, function() {

            });
        },
        getFieldForAttribute: function (attribute)
        {
            switch(attribute.type) {
                case 'pim_catalog_boolean':
                    return new BooleanField(attribute);
                case 'pim_catalog_date':
                    return new DateField(attribute);
                case 'pim_catalog_file':
                case 'pim_catalog_image':
                    return new MediaField(attribute);
                case 'pim_catalog_metric':
                    return new MetricField(attribute);
                case 'pim_catalog_multiselect':
                    return new MultiSelectField(attribute);
                case 'pim_catalog_number':
                    return new NumberField(attribute);
                case 'pim_catalog_price_collection':
                    return new PriceCollectionField(attribute);
                case 'pim_catalog_simpleselect':
                    return new SimpleSelectField(attribute);
                case 'pim_catalog_identifier':
                case 'pim_catalog_text':
                    return new TextField(attribute);
                case 'pim_catalog_textarea':
                    return new TextareaField(attribute);
                default:
                    throw new Error(JSON.stringify(attribute));
            }
        }
    };
});
