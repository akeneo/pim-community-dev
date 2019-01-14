const BaseText = require('pim/form/common/fields/text');

class Text extends BaseText {
  isReadOnly() {
    return !this.getFormData().is_enabled;
  }
}

export = Text;
