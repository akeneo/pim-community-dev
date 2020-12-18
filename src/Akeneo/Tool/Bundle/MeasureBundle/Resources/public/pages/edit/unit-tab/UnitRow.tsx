import React from 'react';
import styled from 'styled-components';
import {ErrorBadge} from 'akeneomeasure/shared/components/ErrorBadge';
import {Unit, UnitCode, getUnitLabel} from 'akeneomeasure/model/unit';
import {useTranslate, useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {Badge, Table} from 'akeneo-design-system';

const UnitCodeContainer = styled.span`
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex: 1;
`;

const UnitCode = styled.span`
  flex: 1;
`;

type UnitRowProps = {
  unit: Unit;
  isStandardUnit: boolean;
  isSelected?: boolean;
  isInvalid?: boolean;
  onRowSelected: (unitCode: UnitCode) => void;
};

const UnitRow = ({unit, isStandardUnit, isSelected = false, isInvalid = false, onRowSelected}: UnitRowProps) => {
  const translate = useTranslate();
  const locale = useUserContext().get('uiLocale');

  return (
    <Table.Row isSelected={isSelected} onClick={() => onRowSelected(unit.code)}>
      <Table.Cell rowTitle={true}>{getUnitLabel(unit, locale)}</Table.Cell>
      <Table.Cell>
        <UnitCodeContainer>
          <UnitCode>{unit.code}</UnitCode>
          {isStandardUnit && <Badge level="tertiary">{translate('measurements.family.standard_unit')}</Badge>}
          {isInvalid && <ErrorBadge />}
        </UnitCodeContainer>
      </Table.Cell>
    </Table.Row>
  );
};

export {UnitRow};
