import React from 'react';
import {useTranslate} from '../../dependenciesTools/hooks';
import {Attribute, MeasurementUnitCode} from '../../models';
import {InputNumber} from './';
import {
  getMeasurementUnitValidator,
  MeasurementUnitSelector,
} from '../Selectors/MeasurementUnitSelector';
import styled from 'styled-components';
import {Router, Translate} from '../../dependenciesTools';
import {
  isMeasurementAmountFilled,
  isMeasurementUnitFilled,
  MeasurementData,
  parseMeasurementValue,
} from '../../models/Measurement';

const MeasurementContainer = styled.div<{hiddenLabel: boolean}>`
  display: flex;
  flex-wrap: ${({hiddenLabel}): string => (hiddenLabel ? 'nowrap' : 'wrap')};
  width: 100%;

  .AknNumberField {
    flex-basis: 50%;
    flex: 1;
    max-width: 50%;
  }

  label {
    flex-basis: 100%;
  }
`;

const MeasurementUnitContainer = styled.span`
  flex-basis: 50%;
  max-width: 50%;
  flex: 1;
`;

MeasurementContainer.displayName = 'MeasurementContainer';

const getMeasurementValidator = (
  attribute: Attribute,
  router: Router,
  translate: Translate
) => {
  return (value: any) => {
    if (!isMeasurementAmountFilled(value)) {
      return translate('pimee_catalog_rule.exceptions.required');
    } else if (!isMeasurementUnitFilled(value)) {
      return translate('pimee_catalog_rule.exceptions.required_unit');
    } else {
      const {validate} = getMeasurementUnitValidator(
        attribute,
        router,
        translate
      );
      return validate(value.unit);
    }
  };
};

type Props = {
  id: string;
  attribute: Attribute;
  value?: MeasurementData;
  onChange: (value: MeasurementData) => void;
  label?: string;
  hiddenLabel?: boolean;
  hasError?: boolean;
};

const InputMeasurement: React.FC<Props> = ({
  id,
  attribute,
  value,
  label,
  onChange,
  hiddenLabel,
  hasError,
}) => {
  const translate = useTranslate();
  const parsedValue = parseMeasurementValue(value);

  const handleAmountChange = (amount: number | null) => {
    onChange({amount, unit: parsedValue.unit});
  };
  const handleUnitChange = (unit: MeasurementUnitCode | null) => {
    onChange({amount: parsedValue.amount, unit});
  };

  return (
    <MeasurementContainer hiddenLabel={!!hiddenLabel}>
      <InputNumber
        data-testid={`${id}-amount`}
        label={label}
        value={null === parsedValue.amount ? '' : parsedValue.amount}
        onChange={(event: any) => handleAmountChange(event.target.value)}
        className={`AknTextField AknNumberField AknTextField--noRightRadius AknNumberField--hideArrows${
          hasError ? ' AknTextField--error' : ''
        }`}
        step={attribute.decimals_allowed ? '' : 1}
        hiddenLabel={hiddenLabel}
      />
      <MeasurementUnitContainer
        className={hasError ? 'select2-glued-container-error' : ''}>
        <MeasurementUnitSelector
          data-testid={`${id}-unit`}
          attribute={attribute}
          value={parsedValue.unit}
          onChange={unit => handleUnitChange(unit)}
          hiddenLabel={true}
          containerCssClass={`select2-container-left-glued select2-container-as-option`}
          placeholder={translate(
            'pimee_catalog_rule.form.edit.actions.set_attribute.select_measurement_unit'
          )}
        />
      </MeasurementUnitContainer>
    </MeasurementContainer>
  );
};

InputMeasurement.displayName = 'InputMeasurement';

export {InputMeasurement, getMeasurementValidator};
