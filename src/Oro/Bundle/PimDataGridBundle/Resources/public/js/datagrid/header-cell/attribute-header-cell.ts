const UserContext = require('pim/user-context');
const FetcherRegistry = require('pim/fetcher-registry');
const HeaderCell = require('oro/datagrid/header-cell');

/**
 * It gets the header attribute label from the database instead of translation file.
 */
class AttributeHeaderCell extends HeaderCell {

    private attributeCode: string;

    initialize(options: any) {
        if (!options.column.has('extraOptions')
            || undefined === options.column.get('extraOptions').attributeCode
        ) {
            throw new Error('The option "attributeCode" must be set for a header cell of type "attribute".');
        }

        this.attributeCode = options.column.get('extraOptions').attributeCode;

        HeaderCell.prototype.initialize.apply(this, arguments);
    }

    render() {
        FetcherRegistry.getFetcher('attribute').fetch(this.attributeCode)
            .then((attribute: any) => {
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
}

export = AttributeHeaderCell;
