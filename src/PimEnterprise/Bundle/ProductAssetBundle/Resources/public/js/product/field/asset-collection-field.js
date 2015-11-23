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
        'pimee/picker/asset-collection'
    ],
    function (
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
            setContext: function () {
                Field.prototype.setContext.apply(this, arguments);

                this.assetCollectionPicker.setContext(this.context);
            },

            /**
             * {@inheritdoc}
             */
            renderInput: function () {
                return this.assetCollectionPicker.render().$el;
            }
        });
    }
);
