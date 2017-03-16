'use strict';

/**
 * Add attribute select view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/common/add-attribute'
    ],
    function (
        $,
        _,
        AddAttribute
    ) {
        return AddAttribute.extend({
            resultsPerPage: 10,
            class: 'AknButtonList-item add-attribute',

            /**
             * {@inheritdoc}
             */
            addAttributes: function () {
                this.getRoot().trigger('add-attribute:add', { codes: this.selection });
            },
            /**
             * {@inheritdoc}
             */
            getExcludedAttributes: function () {
                return $.Deferred().resolve(
                    _.pluck(
                        this.getFormData().attributes,
                        'code'
                    )
                );
            }
        });
    }
);

