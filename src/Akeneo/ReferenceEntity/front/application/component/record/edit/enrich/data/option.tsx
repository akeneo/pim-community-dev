import * as React from 'react';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import Select2 from 'akeneoreferenceentity/application/component/app/select2';
import {OptionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option';
import OptionData, {denormalize as denormalizeOptionData} from 'akeneoreferenceentity/domain/model/record/data/option';
import {NormalizedOption, Option} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';
import __ from 'akeneoreferenceentity/tools/translator';

const View = ({value, onChange, locale}: {value: Value; locale: LocaleReference; onChange: (value: Value) => void}) => {
  if (!(value.data instanceof OptionData)) {
    return null;
  }
  const data = value.data as OptionData;

  const attribute = value.attribute as OptionAttribute;
  const availableOptions = attribute.options.reduce((availableOptions: {[choiceValue: string]: string}, option: Option) => {
    const normalizedOption: NormalizedOption = option.normalize();
    availableOptions[normalizedOption.code] = option.getLabel(locale.stringValue());

    return availableOptions;
  }, {});

  // We have to handle the case where the previous value has an option not in the attribute anymore
  if (!data.isEmpty() &&
    undefined === attribute.options.find((option: Option) => option.code.equals(data.getCode()))
  ) {
    // If the value was not found in the option list, we dispatch a change on the value
    const newData = denormalizeOptionData(null);
    const newValue = value.setData(newData);

    onChange(newValue);
  }

  return (
    <div className="option-selector-container AknSelectField">
      <Select2
        data={availableOptions}
        value={data.stringValue()}
        multiple={false}
        readOnly={false}
        configuration={{
          allowClear: true,
          placeholder: __('pim_reference_entity.attribute.options.no_value'),
        }}
        onChange={(optionCode: string) => {
          const newData = denormalizeOptionData(optionCode);
          const newValue = value.setData(newData);

          onChange(newValue);
        }}
      />
    </div>
  );
};

export const view = View;
