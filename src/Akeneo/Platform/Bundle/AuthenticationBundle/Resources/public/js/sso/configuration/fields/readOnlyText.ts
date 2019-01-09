const BaseText = require('pim/form/common/fields/text');

class ReadOnlyText extends BaseText {
  isReadOnly() {
    return true;
  }
}

export = ReadOnlyText;
