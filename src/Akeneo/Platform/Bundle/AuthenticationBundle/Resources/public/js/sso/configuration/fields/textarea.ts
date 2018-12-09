const BaseTextarea = require('pim/form/common/fields/textarea');

class Textarea extends BaseTextarea {
  isReadOnly() {
    return !this.getFormData().enabled;
  }
}

export = Textarea;
