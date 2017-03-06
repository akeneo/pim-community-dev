'use strict';

/**
 * Add attribute mass-edit select view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/fetcher-registry',
        'pim/common/add-attribute',
        'oro/mediator'
    ],
    function (
        $,
        _,
        FetcherRegistry,
        AddAttribute,
        mediator
    ) {
        return AddAttribute.extend({
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

                return AddAttribute.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template());

                this.$('input[type="hidden"]').prop('readonly', this.lock);

                this.initializeSelectWidget();
                this.delegateEvents();

                return this;
            },

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
                return FetcherRegistry.getFetcher('attribute').getIdentifierAttribute()
                    .then(function (identifier) {
                        var existingAttributes = _.pluck(
                            this.getFormData().attributes,
                            'code'
                        );

                        if (!_.contains(existingAttributes, identifier.code)) {
                            existingAttributes.push(identifier.code);
                        }

                        return existingAttributes;
                    }.bind(this));
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

