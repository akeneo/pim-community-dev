'use strict';

/**
 * Add attributes by group mass-edit select view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'pim/family-edit-form/attributes/toolbar/add-attribute-group',
        'oro/mediator'
    ],
    function (
        AddAttributesByGroupView,
        mediator
    ) {
        return AddAttributesByGroupView.extend({
            resultsPerPage: 10,
            class: 'AknButtonList-item add-attribute',
            lock: false,

            /**
             * {@inheritdoc}
             */
            configure: function () {
                mediator.on(
                    'jstree:lock',
                    this.onLock.bind(this)
                );

                mediator.on(
                    'jstree:unlock',
                    this.onUnlock.bind(this)
                );

                return AddAttributesByGroupView
                    .prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template());

                this.$(this.targetElement).prop('readonly', this.lock);

                this.initializeSelectWidget();
                this.delegateEvents();

                return this;
            },

            /**
             * Lock event callback
             */
            onLock: function () {
                this.lock = true;
                this.render();
            },

            /**
             * Unlock event callback
             */
            onUnlock: function () {
                this.lock = false;
                this.render();
            }
        });
    }
);

