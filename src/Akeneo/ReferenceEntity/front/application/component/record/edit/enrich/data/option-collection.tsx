import * as React from 'react';
import Value from 'akeneoreferenceentity/domain/model/record/value';
// import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import Select2 from "akeneoreferenceentity/application/component/app/select2";
import {OptionAttribute} from "akeneoreferenceentity/domain/model/attribute/type/option";
import {NormalizedOption, Option} from "akeneoreferenceentity/domain/model/attribute/type/option/option";
import OptionCollectionData, {create as createOptionCollectionData} from "akeneoreferenceentity/domain/model/record/data/option-collection";
import {OptionCollectionAttribute} from "akeneoreferenceentity/domain/model/attribute/type/option-collection";

const View = ({
  value,
  onChange,
  // channel,
  locale,
}: {
  value: Value;
  // channel: ChannelReference;
  locale: LocaleReference;
  onChange: (value: Value) => void;
}) => {
  if (!(value.data instanceof OptionCollectionData)) {
    return null;
  }

  const attribute = value.attribute as OptionCollectionAttribute;
  let availableOptionCodes: { [choiceValue: string]: string; } = {};
  availableOptionCodes[''] = '';

  attribute.options.map(
    (option: Option) => {
      const normalizedOption: NormalizedOption = option.normalize();
      availableOptionCodes[normalizedOption.code] = option.getLabel(locale.stringValue());
    }
  );

  return (
    <div className="option-collection-selector-container AknSelectField">
      <Select2
        id=""
        name="select2"
        data={availableOptionCodes}
        value={value.data.isEmpty() ? [] : value.data.normalize()}
        multiple={true}
        readOnly={false}
        configuration={{
          allowClear: true,
          placeholder: ''
        }}
        onChange={(optionCode: string) => {
          const newData = createOptionCollectionData(optionCode);
          const newValue = value.setData(newData);

          onChange(newValue);
        }}
      />
    </div>
  );
};

export const view = View;
