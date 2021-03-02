import * as React from 'react';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
import {isOptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {
  isOptionData,
  optionDataStringValue,
  optionDataFromString,
} from 'akeneoassetmanager/domain/model/asset/data/option';
import {Option, getOptionLabel} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
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
  if (!isOptionData(value.data) || !isOptionAttribute(value.attribute)) {
    return null;
  }

  const availableOptions = value.attribute.options.reduce(
    (availableOptions: {[choiceValue: string]: string}, option: Option) => {
      availableOptions[option.code] = getOptionLabel(option, locale);

      return availableOptions;
    },
    {}
  );

  return (
    <div className="option-selector-container">
      <Select2
        id={`pim_asset_manager.asset.enrich.${value.attribute.code}`}
        className="AknSelectField"
        data={availableOptions}
        value={optionDataStringValue(value.data)}
        multiple={false}
        readOnly={!canEditData}
        configuration={{
          allowClear: true,
          placeholder: translate('pim_asset_manager.attribute.options.no_value'),
        }}
        onChange={(optionCode: string) => {
          //TODO remove old options
          const newData = optionDataFromString(optionCode);
          const newValue = setValueData(value, newData);

          onChange(newValue);
        }}
      />
    </div>
  );
};

export const view = View;
