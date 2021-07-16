const BaseGridTitle = require('pim/common/grid-title');
const DatagridState = require('pim/datagrid/state');

class ProductGridContext extends BaseGridTitle {
  /**
   * {@inheritdoc}
   */
  render(): ProductGridContext {
    const dataGridState = DatagridState.get('product-grid');
    console.log(dataGridState);

    //this.$el.html(__(this.config.title, {count: this.count}, this.count));
    this.$el.html('test');

    return this;
  }
}

export = ProductGridContext;
