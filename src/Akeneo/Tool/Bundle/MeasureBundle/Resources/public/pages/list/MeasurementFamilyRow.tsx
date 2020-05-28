import React from 'react';
import {useHistory} from 'react-router-dom';
import {
  MeasurementFamily,
  getMeasurementFamilyLabel,
  getStandardUnitLabel,
} from 'akeneomeasure/model/measurement-family';
import {LabelCell, Row} from 'akeneomeasure/pages/common/Table';
import {useUserContext} from '@akeneo-pim-community/legacy-bridge';

type MeasurementFamilyRowProps = {
  measurementFamily: MeasurementFamily;
};

const MeasurementFamilyRow = ({measurementFamily}: MeasurementFamilyRowProps) => {
  const locale = useUserContext().get('uiLocale');
  const history = useHistory();
  const measurementFamilyLabel = getMeasurementFamilyLabel(measurementFamily, locale);

  return (
    <Row title={measurementFamilyLabel} onClick={() => history.push(`/${measurementFamily.code}`)}>
      <LabelCell>{measurementFamilyLabel}</LabelCell>
      <td>{measurementFamily.code}</td>
      <td>{getStandardUnitLabel(measurementFamily, locale)}</td>
      <td>{measurementFamily.units.length}</td>
    </Row>
  );
};

export {MeasurementFamilyRow};
