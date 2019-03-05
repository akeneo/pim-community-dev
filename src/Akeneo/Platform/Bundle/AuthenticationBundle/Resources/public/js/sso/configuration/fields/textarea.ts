import * as _ from "underscore"

const BaseTextarea = require('pim/form/common/fields/textarea');
const template = require('pim/template/form/common/fields/textarea-monospaced');

class Textarea extends BaseTextarea {
  readonly template = _.template(template);

  isReadOnly() {
    return !this.getFormData().configuration.is_enabled;
  }
}

export = Textarea;
