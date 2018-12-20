import * as React from 'react';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import Select2 from 'akeneoreferenceentity/application/component/app/select2';
import {OptionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option';
import OptionData, {denormalize as denormalizeOptionData} from 'akeneoreferenceentity/domain/model/record/data/option';
import {NormalizedOption, Option} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';
import __ from 'akeneoreferenceentity/tools/translator';

const View = ({value, onChange, locale, rights}: {
  value: Value;
  locale: LocaleReference;
  onChange: (value: Value) => void;
  rights: {
    record: {
      edit: boolean;
      delete: boolean;
    }
  };
}) => {
  if (!(value.data instanceof OptionData)) {
    return null;
  }
  const data = value.data as OptionData;

  const attribute = value.attribute as OptionAttribute;
  const availableOptions = attribute.options.reduce(
    (availableOptions: {[choiceValue: string]: string}, option: Option) => {
      const normalizedOption: NormalizedOption = option.normalize();
      availableOptions[normalizedOption.code] = option.getLabel(locale.stringValue());

      return availableOptions;
    },
    {}
  );

  return (
    <div className="option-selector-container">
      <Select2
        id={`pim_reference_entity.record.enrich.${value.attribute.getCode().stringValue()}`}
        className="AknSelectField"
        data={availableOptions}
        value={data.stringValue()}
        multiple={false}
        readOnly={!rights.record.edit}
        configuration={{
          allowClear: true,
          placeholder: __('pim_reference_entity.attribute.options.no_value'),
        }}
        onChange={(optionCode: string) => {
          const newData = denormalizeOptionData(optionCode, attribute);
          const newValue = value.setData(newData);

          onChange(newValue);
        }}
      />
    </div>
  );
};

export const view = View;
