import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Field, SelectInput} from 'akeneo-design-system';
import React, {useCallback} from 'react';
import {Filter} from '../../models';

type EnabledFilterType = {
  field: 'enabled';
  operator: '=' | '!=';
  value: boolean;
};

const getFilterValue = (filter: Filter | undefined): 'enabled' | 'disabled' | 'all' => {
  if (filter === undefined) {
    return 'all';
  }

  if (filter.value === true && filter.operator === '=') {
    return 'enabled';
  }

  if (filter.value === false && filter.operator === '=') {
    return 'disabled';
  }

  throw new Error('Filter value is not valid');
};

type EnabledFilterProps = {
  filter: EnabledFilterType | undefined;
  validationErrors: ValidationError[];
  onChange: (updatedFilter: EnabledFilterType | undefined) => void;
};

const EnabledFilter = ({filter, validationErrors, onChange}: EnabledFilterProps) => {
  const translate = useTranslate();

  const handleFilterChange = useCallback(
    (newFilterValue: string | null) => {
      switch (newFilterValue) {
        case 'enabled':
          onChange({
            field: 'enabled',
            operator: '=',
            value: true,
          });
          return;
        case 'disabled':
          onChange({
            field: 'enabled',
            operator: '=',
            value: false,
          });
          return;
        case 'all':
          onChange(undefined);
          return;

        default:
          break;
      }
    },
    [onChange]
  );

  return (
    <Field label={translate('')}>
      <SelectInput
        onChange={handleFilterChange}
        value={getFilterValue(filter)}
        invalid={0 < validationErrors.length}
        clearable={false}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
      >
        <SelectInput.Option title={translate('pim_enrich.export.product.filter.enabled.enabled')} value="enabled">
          {translate('pim_enrich.export.product.filter.enabled.enabled')}
        </SelectInput.Option>
        <SelectInput.Option title={translate('pim_enrich.export.product.filter.enabled.disabled')} value="disabled">
          {translate('pim_enrich.export.product.filter.enabled.disabled')}
        </SelectInput.Option>
        <SelectInput.Option title={translate('pim_enrich.export.product.filter.enabled.all')} value="all">
          {translate('pim_enrich.export.product.filter.enabled.all')}
        </SelectInput.Option>
      </SelectInput>
    </Field>
  );
};

export {EnabledFilter};
export type {EnabledFilterType};
