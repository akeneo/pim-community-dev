import React, {useContext} from 'react';
import styled from 'styled-components';
import {UserContext} from 'akeneomeasure/context/user-context';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {ErrorBadge} from 'akeneomeasure/shared/components/ErrorBadge';
import {Unit, UnitCode, getUnitLabel} from 'akeneomeasure/model/unit';
import {Row, LabelCell} from 'akeneomeasure/pages/common/Table';

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
  invalid?: boolean;
  onRowSelected: (unitCode: UnitCode) => void;
};

const UnitRow = ({unit, isStandardUnit, isSelected = false, invalid = false, onRowSelected}: UnitRowProps) => {
  const __ = useContext(TranslateContext);
  const locale = useContext(UserContext)('uiLocale');

  return (
    <Row role="unit-row" isSelected={isSelected} onClick={() => onRowSelected(unit.code)}>
      <LabelCell>{getUnitLabel(unit, locale)}</LabelCell>
      <CodeCell>
        <span>
          <span>{unit.code}</span>
          {isStandardUnit && <StandardUnitBadge>{__('measurements.family.standard_unit')}</StandardUnitBadge>}
          {invalid && <ErrorBadge />}
        </span>
      </CodeCell>
    </Row>
  );
};

export {UnitRow};
