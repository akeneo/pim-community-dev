/**
 * This module displays a variant field as select2
 * It is adapted for mass edit because it has ability to have readOnly option do disallow edit.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define(
    [
        'pim/product-model/form/creation/variant'
    ],
    function (
        Variant
    ) {
        return Variant.extend({
            readOnly: false,

            /**
             * {@inheritdoc}
             */
            initialize() {
                Variant.prototype.initialize.apply(this, arguments);

                this.readOnly = false;
            },

            /**
             * {@inheritdoc}
             */
            configure() {
                this.listenTo(
                    this,
                    'mass-edit:update-read-only',
                    this.setReadOnly.bind(this)
                );

                return Variant.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            isReadOnly() {
                return this.readOnly || !this.getFormData().family
            },

            /**
             * Updates the readOnly parameter to avoid edition of the field
             *
             * @param {Boolean} readOnly
             */
            setReadOnly(readOnly) {
                this.readOnly = readOnly;
            }
        });
    }
);
