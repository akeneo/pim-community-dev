import React from 'react';
import styled from 'styled-components';
import {Unit, UnitCode, getUnitLabel} from '../../../model/unit';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Badge, Pill, Table} from 'akeneo-design-system';

const UnitCodeContainer = styled.span`
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex: 1;
`;

const UnitCodeLabel = styled.span`
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
          <UnitCodeLabel>{unit.code}</UnitCodeLabel>
          {isStandardUnit && <Badge level="tertiary">{translate('measurements.family.standard_unit')}</Badge>}
          {isInvalid && <Pill level="danger" />}
        </UnitCodeContainer>
      </Table.Cell>
    </Table.Row>
  );
};

export {UnitRow};
