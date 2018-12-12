const BaseTextarea = require('pim/form/common/fields/textarea');

class Textarea extends BaseTextarea {
  isReadOnly() {
    return !this.getFormData().is_enabled;
  }
}

export = Textarea;
