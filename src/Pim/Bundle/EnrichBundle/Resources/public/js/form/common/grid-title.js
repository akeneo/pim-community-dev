'use strict';
/**
 * Grid title extension
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/form',
        'oro/translator',
        'oro/mediator'
    ], function (
        BaseForm,
        __,
        mediator
    ) {
        return BaseForm.extend({
            count: null,

            /**
             * {@inheritdoc}
             */
            initialize(config) {
                this.config = config.config;

                mediator.once('grid_load:start', this.setupCollection.bind(this));
                mediator.on('grid_load:complete', this.setupCollection.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            render() {
                if (null !== this.count) {
                    this.$el.text(
                        __(this.config.title, {count: this.count}, this.count)
                    );
                }
            },

            /**
             * Setup the count from the collection
             *
             * @param {Object} collection
             */
            setupCollection(collection) {
                this.count = collection.state.totalRecords;

                this.render();
            }
        });
    }
);
