import * as React from 'react';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
import {Option, getOptionLabel} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {
  isOptionCollectionData,
  optionCollectionDataArrayValue,
  optionCollectionDataFromArray,
} from 'akeneoassetmanager/domain/model/asset/data/option-collection';
import {isOptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {setValueData} from 'akeneoassetmanager/domain/model/asset/value';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const View = ({
  value,
  onChange,
  locale,
  canEditData,
}: {
  value: EditionValue;
  locale: LocaleReference;
  onChange: (value: EditionValue) => void;
  canEditData: boolean;
}) => {
  const translate = useTranslate();
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
          placeholder: translate('pim_asset_manager.attribute.options.no_value'),
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
