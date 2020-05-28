import React from 'react';
import styled from 'styled-components';
import {ErrorBadge} from 'akeneomeasure/shared/components/ErrorBadge';
import {Unit, UnitCode, getUnitLabel} from 'akeneomeasure/model/unit';
import {Row, LabelCell} from 'akeneomeasure/pages/common/Table';
import {useTranslate, useUserContext} from '@akeneo-pim-community/legacy-bridge';

const CodeCell = styled.td`
  padding-right: 15px;

  > span {
    display: flex;
    align-items: center;

    span:first-child {
      flex: 1;
    }
  }
`;

const StandardUnitBadge = styled.span`
  background-color: white;
  border: 1px solid ${props => props.theme.color.grey100};
  font-size: ${props => props.theme.fontSize.small};
  color: ${props => props.theme.color.grey120};
  border-radius: 2px;
  padding: 0 5px;
  text-transform: uppercase;

  :not(:last-child) {
    margin-right: 15px;
  }
`;

type UnitRowProps = {
  unit: Unit;
  isStandardUnit: boolean;
  isSelected?: boolean;
  isInvalid?: boolean;
  onRowSelected: (unitCode: UnitCode) => void;
};

const UnitRow = ({unit, isStandardUnit, isSelected = false, isInvalid = false, onRowSelected}: UnitRowProps) => {
  const __ = useTranslate();
  const locale = useUserContext().get('uiLocale');

  return (
    <Row role="unit-row" isSelected={isSelected} onClick={() => onRowSelected(unit.code)}>
      <LabelCell>{getUnitLabel(unit, locale)}</LabelCell>
      <CodeCell>
        <span>
          <span>{unit.code}</span>
          {isStandardUnit && <StandardUnitBadge>{__('measurements.family.standard_unit')}</StandardUnitBadge>}
          {isInvalid && <ErrorBadge />}
        </span>
      </CodeCell>
    </Row>
  );
};

export {UnitRow};
