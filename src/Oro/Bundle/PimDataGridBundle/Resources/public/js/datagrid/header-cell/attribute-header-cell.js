define(
    [
        'oro/datagrid/header-cell',
        'pim/fetcher-registry',
        'pim/user-context',
    ],
    function (
        HeaderCell,
        FetcherRegistry,
        UserContext
    ) {
        'use strict';

        return HeaderCell.extend({
            attributeCode: null,

            initialize: function(options) {
                if (!options.column.has('extraOptions')
                    || undefined === options.column.get('extraOptions').attributeCode
                ) {
                    throw new Error('The option "attributeCode" must be set for a header cell of type "attribute".');
                }

                this.attributeCode = options.column.get('extraOptions').attributeCode;

                HeaderCell.prototype.initialize.apply(this, arguments);
            },

            render: function () {
                FetcherRegistry.getFetcher('attribute').fetch(this.attributeCode)
                    .then((attribute) => {
                        this.$el.html($(this.template({
                            label: attribute.labels[UserContext.get('catalogLocale')],
                            sortable: this.column.get('sortable')
                        })));

                        if (this.column.has('width')) {
                            this.$el.width(this.column.get('width'));
                        }

                    });

                return this;
            }
        });
    }
);
