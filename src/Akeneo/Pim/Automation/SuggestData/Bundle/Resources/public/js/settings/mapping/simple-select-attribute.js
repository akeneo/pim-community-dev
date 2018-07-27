'use strict';

/**
 * Attributes simple select
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define(
    ['pim/form/common/fields/simple-select-async'],
    BaseSimpleSelect => {
        return BaseSimpleSelect.extend({
            className: 'AknFieldContainer AknFieldContainer--withoutMargin',

            /**
             * {@inheritdoc}
             */
            getSelect2Options() {
                const parent = BaseSimpleSelect.prototype.getSelect2Options.apply(this, arguments);
                parent.allowClear = true;

                return parent;
            },

            /**
             * {@inheritdoc}
             *
             * Removes the useless catalogLocale field, and adds localizable, is_locale_specific and scopable filters.
             */
            select2Data(term, page) {
                return {
                    localizable: false,
                    is_locale_specific: false,
                    scopable: false,
                    search: term,
                    options: {
                        limit: this.resultsPerPage,
                        page: page
                    }
                };
            }
        });
    }
);
