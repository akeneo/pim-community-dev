import React, {useContext} from 'react';
import {useHistory} from 'react-router-dom';
import {
  MeasurementFamily,
  getMeasurementFamilyLabel,
  getStandardUnitLabel,
} from 'akeneomeasure/model/measurement-family';
import {UserContext} from 'akeneomeasure/context/user-context';
import {LabelCell, Row} from 'akeneomeasure/pages/common/Table';

type MeasurementFamilyRowProps = {
  measurementFamily: MeasurementFamily;
};

const MeasurementFamilyRow = ({measurementFamily}: MeasurementFamilyRowProps) => {
  const locale = useContext(UserContext)('uiLocale');
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
