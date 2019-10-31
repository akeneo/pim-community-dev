import * as React from 'react';
import Value from 'akeneoassetmanager/domain/model/asset/value';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
import {OptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import OptionData, {denormalize as denormalizeOptionData} from 'akeneoassetmanager/domain/model/asset/data/option';
import {NormalizedOption, Option} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import __ from 'akeneoassetmanager/tools/translator';

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
  if (!(value.data instanceof OptionData)) {
    return null;
  }
  const data = value.data as OptionData;

  const attribute = value.attribute as OptionAttribute;
  const availableOptions = attribute.options.reduce(
    (availableOptions: {[choiceValue: string]: string}, option: Option) => {
      const normalizedOption: NormalizedOption = option.normalize();
      availableOptions[normalizedOption.code] = option.getLabel(localeReferenceStringValue(locale));

      return availableOptions;
    },
    {}
  );

  return (
    <div className="option-selector-container">
      <Select2
        id={`pim_asset_manager.asset.enrich.${value.attribute.getCode()}`}
        className="AknSelectField"
        data={availableOptions}
        value={data.stringValue()}
        multiple={false}
        readOnly={!canEditData}
        configuration={{
          allowClear: true,
          placeholder: __('pim_asset_manager.attribute.options.no_value'),
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
