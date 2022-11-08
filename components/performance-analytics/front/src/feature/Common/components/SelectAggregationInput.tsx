import React from 'react';
import {SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
  filters: string[];
  value: string;
  onChange: (value: string) => void;
};

const SelectAggregationInput = ({filters, value, onChange}: Props) => {
  const translate = useTranslate();

  return (
    <SelectInput
      title={translate('akeneo.performance_analytics.control_panel.select_input.metric_title')}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.open')}
      value={value}
      onChange={onChange}
      clearable={false}
    >
      {filters.map(name => (
        <SelectInput.Option value={name} key={name}>
          <div>{translate('akeneo.performance_analytics.control_panel.select_input.aggregations.' + name)}</div>
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export {SelectAggregationInput};
