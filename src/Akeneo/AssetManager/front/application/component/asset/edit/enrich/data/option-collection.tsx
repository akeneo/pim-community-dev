import * as React from 'react';
import Value, {setValueData} from 'akeneoassetmanager/domain/model/asset/value';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
import {Option, getOptionLabel} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {
  isOptionCollectionData,
  optionCollectionDataArrayValue,
  optionCollectionDataFromArray,
} from 'akeneoassetmanager/domain/model/asset/data/option-collection';
import {isOptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
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
  if (!isOptionCollectionData(value.data) || !isOptionCollectionAttribute(value.attribute)) {
    return null;
  }

  const formatedOptions = value.attribute.options.reduce(
    (formatedOptions: {[code: string]: string}, option: Option) => {
      formatedOptions[option.code] = getOptionLabel(option, locale);

      return formatedOptions;
    },
    {}
  );

  return (
    <div className="option-collection-selector-container AknSelectField">
      <Select2
        id={`pim_asset_manager.asset.enrich.${value.attribute.code}`}
        className="AknSelectField"
        data={formatedOptions}
        value={optionCollectionDataArrayValue(value.data)}
        multiple={true}
        readOnly={!canEditData}
        configuration={{
          allowClear: true,
          placeholder: __('pim_asset_manager.attribute.options.no_value'),
        }}
        onChange={(optionCodes: string[]) => {
          //TODO: remove old options
          const newData = optionCollectionDataFromArray(optionCodes);
          const newValue = setValueData(value, newData);

          onChange(newValue);
        }}
      />
    </div>
  );
};

export const view = View;
