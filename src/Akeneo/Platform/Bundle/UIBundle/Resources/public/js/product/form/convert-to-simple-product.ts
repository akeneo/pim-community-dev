import BaseConvertToSimpleProduct = require('pim/product-edit-form/convert-to-simple-product');

class ConvertToSimpleProduct extends BaseConvertToSimpleProduct {
  protected isAuthorized(): boolean {
    return true === this.getFormData().meta.is_owner;
  }
}

export = ConvertToSimpleProduct;
