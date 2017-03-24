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
    ],
    function (
        _,
        Field,
        AssetCollectionPicker
    ) {
        return Field.extend({
            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.assetCollectionPicker = new AssetCollectionPicker();

                this.assetCollectionPicker.on('collection:change', function (assets) {
                    this.setCurrentValue(assets);
                }.bind(this));

                Field.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            setValues: function () {
                Field.prototype.setValues.apply(this, arguments);

                this.assetCollectionPicker.setData(this.getCurrentValue().data);
            },

            /**
             * {@inheritdoc}
             */
            renderInput: function (templateContext) {
                var context = _.extend({}, this.context, {editMode: templateContext.editMode});
                this.assetCollectionPicker.setContext(context);

                return this.assetCollectionPicker.render().$el;
            }
        });
    }
);
