import React from 'react';
import {
  isOptionCollectionData,
  optionCollectionDataFromArray,
} from 'akeneoassetmanager/domain/model/asset/data/option-collection';
import {isOptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {MultiSelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getOptionLabel} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {ViewGeneratorProps} from 'akeneoassetmanager/application/configuration/value';
import {setValueData} from 'akeneoassetmanager/domain/model/asset/value';

const View = ({value, id, locale, canEditData, invalid, onChange}: ViewGeneratorProps) => {
  const translate = useTranslate();

  if (!isOptionCollectionData(value.data) || !isOptionCollectionAttribute(value.attribute)) {
    return null;
  }

  if (id === undefined) {
    id = `pim_asset_manager.asset.enrich.${value.attribute.code}`;
  }

  return (
    <MultiSelectInput
      id={id}
      value={value.data ?? []}
      placeholder={translate('pim_asset_manager.attribute.options.no_value')}
      emptyResultLabel={translate('pim_asset_manager.result_counter', {count: 0}, 0)}
      removeLabel={translate('pim_common.remove')}
      readOnly={!canEditData}
      invalid={invalid}
      onChange={newOptions => {
        const newValue = setValueData(value, optionCollectionDataFromArray(newOptions));

        onChange(newValue);
      }}
    >
      {value.attribute.options.map(option => (
        <MultiSelectInput.Option key={option.code} value={option.code}>
          {getOptionLabel(option, locale)}
        </MultiSelectInput.Option>
      ))}
    </MultiSelectInput>
  );
};

export const view = View;
