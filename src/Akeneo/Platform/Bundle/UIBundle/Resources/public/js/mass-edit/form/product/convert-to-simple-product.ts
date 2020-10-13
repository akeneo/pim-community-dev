const BaseOperation = require('pim/mass-edit-form/product/operation');

class ConvertToSimpleProduct extends BaseOperation {
  public render() {
    this.$el.text('');

    return this;
  }
}

export = ConvertToSimpleProduct;
