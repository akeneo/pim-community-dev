import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {isOptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import OptionData from 'akeneoassetmanager/domain/model/asset/data/option';
import {ViewGeneratorProps} from 'akeneoassetmanager/application/configuration/value';
import {SelectInput} from 'akeneo-design-system';
import {isOptionData} from 'akeneoassetmanager/domain/model/asset/data/option';
import {getOptionLabel} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {setValueData} from 'akeneoassetmanager/domain/model/asset/value';

const View = ({
  id,
  value,
  onChange,
  locale,
  canEditData,
  invalid
}: ViewGeneratorProps) => {
  const translate = useTranslate();
  if (!isOptionData(value.data) || !isOptionAttribute(value.attribute)) {
    return null;
  }

  if (id === undefined) {
    id = `pim_asset_manager.asset.enrich.${value.attribute.code}`;
  }

  return (
    <SelectInput
      id={id}
      value={value.data}
      readOnly={!canEditData}
      invalid={invalid}
      placeholder={translate('pim_asset_manager.attribute.options.no_value')}
      emptyResultLabel={translate('pim_asset_manager.result_counter', {count: 0}, 0)}
      clearLabel={translate('pim_common.remove')}
      onChange={(optionCode: OptionData) => {
        const newValue = setValueData(value, optionCode);

        onChange(newValue);
      }}
    >
      {value.attribute.options.map(option => (
        <SelectInput.Option key={option.code} value={option.code}>
          {getOptionLabel(option, locale)}
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export const view = View;
