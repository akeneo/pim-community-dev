'use strict';

/**
 * TODO
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define([
    'underscore',
    'pim/form',
    'pim/fetcher-registry',
    'pimee/template/settings/mapping/attributes-mapping',
], function (
        _,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render() {
                FetcherRegistry.getFetcher('suggest_data_family_mapping')
                    .fetch('camcorders')
                    .then((family) => {
                        console.log(family)
                    });

                this.$el.html(this.template({}));

                return this;
            }
        })
    }
);
