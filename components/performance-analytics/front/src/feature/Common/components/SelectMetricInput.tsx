import React from 'react';
import {SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {BigPill} from './BigPill';

const AlignOption = styled.div`
  display: flex;
  align-items: center;
  gap: 5px;
`;

type Props = {
  filters: string[];
  value: string;
  onChange: (value: string) => void;
};

const SelectMetricInput = ({filters, value, onChange}: Props) => {
  const translate = useTranslate();

  return (
    <SelectInput
      title={translate('akeneo.performance_analytics.control_panel.select_input.metric_title')}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.open')}
      value={value}
      readOnly={true}
      onChange={onChange}
      clearable={false}
    >
      {filters.map(name => (
        <SelectInput.Option value={name} key={name}>
          <AlignOption>
            <BigPill />
            <div>{translate('akeneo.performance_analytics.control_panel.select_input.metrics.' + name)}</div>
          </AlignOption>
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export {SelectMetricInput};
