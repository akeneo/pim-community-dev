'use strict';
/**
 * Asset collection field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
define(
    [
        'underscore',
        'pim/field',
        'pimee/picker/asset-collection'
    ], (
        _,
        Field,
        AssetCollectionPicker
    ) => {
        return Field.extend({
            /**
             * {@inheritdoc}
             */
            initialize() {
                this.assetCollectionPicker = new AssetCollectionPicker();

                this.assetCollectionPicker.on('collection:change', function (assets) {
                    this.setCurrentValue(assets);
                }.bind(this));

                Field.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            setValues() {
                Field.prototype.setValues.apply(this, arguments);

                this.assetCollectionPicker.setData(this.getCurrentValue().data);
            },

            /**
             * {@inheritdoc}
             */
            renderInput(templateContext) {
                const entityType = templateContext.context.entity.meta.model_type;
                const entityIdentifier = 'product_model' === entityType
                    ? templateContext.context.entity.code
                    : templateContext.context.entity.identifier;

                const context = _.extend(
                    {},
                    this.context,
                    {editMode: templateContext.editMode},
                    {attributeCode: templateContext.attribute.code},
                    {entityIdentifier: entityIdentifier},
                    {entityType: entityType}
                );

                this.assetCollectionPicker.setContext(context);

                return this.assetCollectionPicker.render().$el;
            },

            /**
             * {@inheritdoc}
             */
            setFocus() {
                this.el.scrollIntoView(false);
            }
        });
    }
);
