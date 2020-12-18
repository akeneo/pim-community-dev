import React from 'react';
import {ErrorBadge} from 'akeneomeasure/shared/components/ErrorBadge';
import {Unit, UnitCode, getUnitLabel} from 'akeneomeasure/model/unit';
import {useTranslate, useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {Badge, Table} from 'akeneo-design-system';

//TODO here fix Badge style & checkbox display
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
        <span>{unit.code}</span>
        {isStandardUnit && <Badge level="tertiary">{translate('measurements.family.standard_unit')}</Badge>}
        {isInvalid && <ErrorBadge />}
      </Table.Cell>
    </Table.Row>
  );
};

export {UnitRow};
