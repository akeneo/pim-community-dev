import React from 'react';
import styled from 'styled-components';
import { useUserCatalogLocale } from '../../../../../dependenciesTools/hooks';
import { InputNumber } from '../../../../../components/Inputs';
import { InputValueProps } from './AttributeValue';
import { getAttributeLabel, MeasurementUnitCode } from '../../../../../models';
import { MeasurementUnitSelector } from '../../../../../components/Selectors/MeasurementUnitSelector';

const MetricValueContainer = styled.div`
  display: flex;
  flex-wrap: wrap;

  .AknNumberField {
    flex-basis: 50%;
  }

  .select2-container {
    flex-basis: 50%;
  }

  label {
    flex-basis: 100%;
  }
`;

type MetricValueData = {
  amount: number | null;
  unit: MeasurementUnitCode;
};

const isMetricValueFilled = (value: any) => {
  return (
    Object.prototype.hasOwnProperty.call(value, 'amount') &&
    Object.prototype.hasOwnProperty.call(value, 'unit') &&
    value.amount !== '' &&
    value.amount !== null &&
    value.unit
  );
};

const parseMetricValue = (value: any): MetricValueData => {
  if (
    Object.prototype.hasOwnProperty.call(value, 'amount') &&
    Object.prototype.hasOwnProperty.call(value, 'unit')
  ) {
    return { amount: value.amount, unit: value.unit };
  }

  return { amount: null, unit: '' };
};

const MetricValue: React.FC<InputValueProps> = ({
  id,
  attribute,
  value,
  label,
  onChange,
}) => {
  const catalogLocale = useUserCatalogLocale();

  const handleAmountChange = (amount: number) => {
    onChange({ amount, unit: value.unit });
  };
  const handleUnitChange = (unit: MeasurementUnitCode | null) => {
    onChange({ amount: value.amount, unit });
  };

  return (
    <MetricValueContainer>
      <InputNumber
        data-testid={id}
        label={label || getAttributeLabel(attribute, catalogLocale)}
        value={value.amount || ''}
        onChange={(event: any) => handleAmountChange(event.target.value)}
        className={'AknTextField AknNumberField AknTextField--noRightRadius'}
      />
      <MeasurementUnitSelector
        attribute={attribute}
        value={value.unit || ''}
        onChange={value => handleUnitChange(value)}
        hiddenLabel={true}
        containerCssClass={
          'select2-container-left-glued select2-container-as-option'
        }
      />
    </MetricValueContainer>
  );
};

export { MetricValue, parseMetricValue, isMetricValueFilled };
