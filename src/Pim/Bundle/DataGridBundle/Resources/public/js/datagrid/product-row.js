define(
    [
        'jquery',
        'underscore',
        'backgrid',
        'oro/datagrid/row',
        'pim/template/datagrid/row/product'
    ],
    function(
        $,
        _,
        Backgrid,
        BaseRow,
        template
    ) {
        return BaseRow.extend({
            tagName: 'div',
            template: _.template(template),
            selectedClass: 'AknGrid-bodyRow--checked',
            setCheckedClass(row) {
                const isChecked = $('.AknGrid-bodyCell--checkbox input:checked', row).length;
                row.toggleClass(this.selectedClass, 1 === isChecked);
            },
            render() {
                const row = $(this.template({}));

                this.$el.empty().html(row);

                for (let i = 0; i < this.cells.length; i++) {
                    const cell = this.cells[i];
                    this.$('.AknGrid-bodyRow').append(cell.render().el);
                }

                console.log(this.$el);

                this.$(row).on('click', this.onClick.bind(this));
                this.$(row).on('change', 'input[type="checkbox"]', this.setCheckedClass.bind(this, row));

                return this.delegateEvents();
            }
        });
    });
