import * as React from 'react';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import Select2 from 'akeneoreferenceentity/application/component/app/select2';
import {NormalizedOption, Option} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';
import OptionCollectionData, {
  denormalize as denormalizeOptionCollectionData,
} from 'akeneoreferenceentity/domain/model/record/data/option-collection';
import {OptionCollectionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option-collection';
import __ from 'akeneoreferenceentity/tools/translator';

const View = ({
  value,
  onChange,
  locale,
  canEditData,
}: {
  value: Value;
  locale: LocaleReference;
  onChange: (value: Value) => void;
  canEditData: boolean;
}) => {
  if (!(value.data instanceof OptionCollectionData)) {
    return null;
  }

  const data = value.data as OptionCollectionData;

  const attribute = value.attribute as OptionCollectionAttribute;

  const formatedOptions = attribute.options.reduce((formatedOptions: {[code: string]: string}, option: Option) => {
    const normalizedOption: NormalizedOption = option.normalize();
    formatedOptions[normalizedOption.code] = option.getLabel(locale.stringValue());

    return formatedOptions;
  }, {});

  return (
    <div className="option-collection-selector-container AknSelectField">
      <Select2
        id={`pim_reference_entity.record.enrich.${value.attribute.getCode().stringValue()}`}
        className="AknSelectField"
        data={formatedOptions}
        value={data.isEmpty() ? [] : data.normalize()}
        multiple={true}
        readOnly={!canEditData}
        configuration={{
          allowClear: true,
          placeholder: __('pim_reference_entity.attribute.options.no_value'),
        }}
        onChange={(optionCodes: string[]) => {
          const newData = denormalizeOptionCollectionData(optionCodes, attribute);
          const newValue = value.setData(newData);

          onChange(newValue);
        }}
      />
    </div>
  );
};

export const view = View;
