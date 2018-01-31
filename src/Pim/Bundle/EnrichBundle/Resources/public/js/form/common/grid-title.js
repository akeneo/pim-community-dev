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

                mediator.once('grid_load:start', this.setupCount.bind(this));
                mediator.on('grid_load:complete', this.setupCount.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            render() {
                if (null !== this.count) {
                    this.$el.text(
                        __(this.config.title, {count: this.count}, this.count)
                    );
                } else if (false === this.config.countable) {
                    this.$el.text(
                        __(this.config.title)
                    );
                }
            },

            /**
             * Setup the count from the collection
             *
             * @param {Object} collection
             */
            setupCount(collection) {
                this.count = collection.state.totalRecords;

                this.render();
            }
        });
    }
);
