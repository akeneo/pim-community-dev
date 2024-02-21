const BaseSelect = require('pim/form/common/fields/select');

type ModelValue = 'false' | 'true' | '';
type FieldValue = boolean | null;

class DefaultBoolValue extends BaseSelect {
  initialize(meta: any): void {
    meta.config.choices = {
      false: 'pim_common.no',
      true: 'pim_common.yes',
    };
    super.initialize(meta);
  }

  getFieldValue(field: string): FieldValue {
    const value = super.getFieldValue(field);

    return 'false' === value ? false : 'true' === value ? true : null;
  }

  getModelValue(): ModelValue {
    const modelValue = super.getModelValue();

    return true === modelValue ? 'true' : false === modelValue ? 'false' : '';
  }
}

export = DefaultBoolValue;
