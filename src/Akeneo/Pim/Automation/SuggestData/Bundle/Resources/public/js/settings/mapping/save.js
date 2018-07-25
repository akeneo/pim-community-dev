'use strict';

/**
 * Save extension identifiers mapping
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
define(
    [
        'jquery',
        'oro/translator',
        'pim/form/common/save',
        'oro/messenger',
        'pimee/saver/identifier-mapping'
    ], (
        $,
        __,
        BaseSave,
        messenger,
        MappingSaver
    ) => {
        return BaseSave.extend({
            updateSuccessMessage: __('akeneo_suggest_data.settings.index.identifiers_mapping.save.flash.success'),
            updateFailureMessage: __('akeneo_suggest_data.settings.index.identifiers_mapping.save.flash.fail'),

            /**
             * {@inheritdoc}
             */
            save: function () {
                let identifiersMapping = $.extend(true, {}, this.getFormData());

                this.showLoadingMask();
                this.getRoot().trigger('pim_enrich:form:entity:pre_save');
                this.cleanMapping(identifiersMapping);

                return MappingSaver
                    .save(null, identifiersMapping, 'POST')
                    .then(savedMapping => {
                        this.postSave();
                        this.setData(JSON.parse(savedMapping));
                    })
                    .fail(this.fail.bind(this))
                    .always(this.hideLoadingMask.bind(this));
            },

            /**
             * When you clear data in select2 choice it puts an empty string instead of null.
             * This function put null instead of empty string in mapping values.
             */
            cleanMapping: function (identifiersMapping) {
                Object.keys(identifiersMapping).map(index => {
                    if ('' === identifiersMapping[index]) {
                        identifiersMapping[index] = null;
                    }
                });

                return identifiersMapping;
            }
        });
    }
);
