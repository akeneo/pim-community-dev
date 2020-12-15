import React from 'react';
import {useHistory} from 'react-router-dom';
import {
  MeasurementFamily,
  getMeasurementFamilyLabel,
  getStandardUnitLabel,
} from 'akeneomeasure/model/measurement-family';
import {LabelCell} from 'akeneomeasure/pages/common/Table';
import {useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {Table} from 'akeneo-design-system';

type MeasurementFamilyRowProps = {
  measurementFamily: MeasurementFamily;
};

const MeasurementFamilyRow = ({measurementFamily}: MeasurementFamilyRowProps) => {
  const locale = useUserContext().get('uiLocale');
  const history = useHistory();
  const measurementFamilyLabel = getMeasurementFamilyLabel(measurementFamily, locale);

  return (
    <Table.Row onClick={() => history.push(`/${measurementFamily.code}`)}>
      <LabelCell>{measurementFamilyLabel}</LabelCell>
      <Table.Cell>{measurementFamily.code}</Table.Cell>
      <Table.Cell>{getStandardUnitLabel(measurementFamily, locale)}</Table.Cell>
      <Table.Cell>{measurementFamily.units.length}</Table.Cell>
    </Table.Row>
  );
};

export {MeasurementFamilyRow};
