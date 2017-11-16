/**
 * This module displays a family with select2 for mass edit
 * The difference with standard select2 async is that this will listen to an event to enable or disable the element.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define(
    [
        'pim/form/common/fields/simple-select-async'
    ],
    function (
        SimpleSelectAsync,
    ) {
        return SimpleSelectAsync.extend({
            readOnly: false,

            /**
             * {@inheritdoc}
             */
            initialize() {
                SimpleSelectAsync.prototype.initialize.apply(this, arguments);

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

                return SimpleSelectAsync.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            isReadOnly() {
                return this.readOnly || SimpleSelectAsync.prototype.isReadOnly.apply(this, arguments);
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
