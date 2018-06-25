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
                const context = _.extend(
                    {},
                    this.context,
                    {editMode: templateContext.editMode},
                    {attributeCode: templateContext.attribute.code}
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
