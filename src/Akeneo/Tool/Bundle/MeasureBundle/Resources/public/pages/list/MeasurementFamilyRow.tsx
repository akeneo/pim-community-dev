import React, {useContext} from 'react';
import {withRouter} from 'react-router-dom';
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

const MeasurementFamilyRow = withRouter(({history, measurementFamily}: MeasurementFamilyRowProps & any) => {
  const locale = useContext(UserContext)('uiLocale');

  return (
    <Row
      onClick={() => {
        history.push(`/configuration/measurement/${measurementFamily.code}`); //TODO maybe do something beter (use the router)
      }}
    >
      <LabelCell>{getMeasurementFamilyLabel(measurementFamily, locale)}</LabelCell>
      <td>{measurementFamily.code}</td>
      <td>{getStandardUnitLabel(measurementFamily, locale)}</td>
      <td>{measurementFamily.units.length}</td>
    </Row>
  );
});

export {MeasurementFamilyRow};
