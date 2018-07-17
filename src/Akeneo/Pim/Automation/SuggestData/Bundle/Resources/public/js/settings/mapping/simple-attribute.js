'use strict';

/**
 * Attributes simple select
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'pim/form/common/fields/simple-select-async'
    ],
    function (
        BaseSimpleSelect
    ) {
        return BaseSimpleSelect.extend({
            /**
             * {@inheritdoc}
             */
            getSelect2Options() {
                const parent = BaseSimpleSelect.prototype.getSelect2Options.apply(this, arguments);
                parent.allowClear = true;

                return parent;
            },
        });
    }
);
