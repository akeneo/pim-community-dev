import React from 'react';
import {DataType} from '../models';
import {useTranslate, useFeatureFlags} from '@akeneo-pim-community/shared';
import {SelectInput} from 'akeneo-design-system';

type DataTypeSelectorProps = {
  dataType: DataType | null;
  onChange: (dataType: DataType | null) => void;
  isFirstColumn: boolean;
};

const DataTypeSelector: React.FC<DataTypeSelectorProps> = ({dataType, onChange, isFirstColumn}) => {
  const translate = useTranslate();
  const featureFlags = useFeatureFlags();

  const dataTypesMapping: {[dataType: string]: {useable_as_first_column: boolean; flag?: string}} = {
    select: {useable_as_first_column: true},
    text: {useable_as_first_column: false},
    number: {useable_as_first_column: false},
    boolean: {useable_as_first_column: false},
    reference_entity: {useable_as_first_column: true, flag: 'reference_entity'},
    measurement: {useable_as_first_column: false},
  };

  const dataTypes: DataType[] = Object.keys(dataTypesMapping).filter((dataType: string) => {
    const dataTypeMapping = dataTypesMapping[dataType];
    if (
      !dataTypeMapping ||
      (typeof dataTypeMapping.flag !== 'undefined' && !featureFlags.isEnabled(dataTypeMapping.flag))
    ) {
      return false;
    }
    return !isFirstColumn || dataTypeMapping.useable_as_first_column;
  }) as DataType[];

  return (
    <SelectInput
      emptyResultLabel={translate('pim_common.select2.no_match')}
      onChange={(value: string | null) => {
        onChange((value || null) as DataType);
      }}
      openLabel={translate('pim_common.open')}
      placeholder={translate('pim_table_attribute.form.attribute.select_type')}
      value={dataType}
      clearable={false}
    >
      {dataTypes.map(dataType => (
        <SelectInput.Option
          key={dataType}
          title={translate(`pim_table_attribute.properties.data_type.${dataType}`)}
          value={dataType}
        >
          {translate(`pim_table_attribute.properties.data_type.${dataType}`)}
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export {DataTypeSelector};
