'use strict';

/**
 * Family mass edit attributes requirements table view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'pim/family-edit-form/attributes/attributes',
        'oro/mediator'
    ],
    function (
        BaseAttributesView,
        mediator
    ) {
        return BaseAttributesView.extend({
            lock: false,

            /**
             * {@inheritdoc}
             */
            configure: function () {
                mediator.on(
                    'mass-edit:form:lock',
                    this.onLock.bind(this)
                );

                mediator.on(
                    'mass-edit:form:unlock',
                    this.onUnlock.bind(this)
                );

                return BaseAttributesView.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            toggleAttribute: function () {
                if (this.lock) {
                    return false;
                }

                BaseAttributesView.prototype.toggleAttribute.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            onRemoveAttribute: function () {
                if (this.lock) {
                    return false;
                }

                BaseAttributesView.prototype.onRemoveAttribute.apply(this, arguments);
            },

            /**
             * Lock event callback
             */
            onLock: function () {
                this.lock = true;
            },

            /**
             * Unlock event callback
             */
            onUnlock: function () {
                this.lock = false;
            }
        });
    }
);
