import React, {useContext} from 'react';
import styled from 'styled-components';
import {
  MeasurementFamily,
  getMeasurementFamilyLabel,
  getStandardUnitLabel,
} from 'akeneomeasure/model/measurement-family';
import {UserContext} from 'akeneomeasure/context/user-context';
import {withRouter} from 'react-router-dom';

const Container = styled.tr`
  height: 54px;
  border-bottom: 1px solid ${props => props.theme.color.grey70};
`;

const MeasurementFamilyLabelCell = styled.td`
  color: ${props => props.theme.color.purple100};
  font-style: italic;
  font-weight: bold;
`;

type MeasurementFamilyRowProps = {
  measurementFamily: MeasurementFamily;
};

const MeasurementFamilyRow = withRouter(({history, measurementFamily}: MeasurementFamilyRowProps & any) => {
  const locale = useContext(UserContext)('uiLocale');

  return (
    <Container
      onClick={() => {
        history.push(`/configuration/measurement/${measurementFamily.code}`); //TODO maybe do something beter (use the router)
      }}
    >
      <MeasurementFamilyLabelCell>{getMeasurementFamilyLabel(measurementFamily, locale)}</MeasurementFamilyLabelCell>
      <td>{measurementFamily.code}</td>
      <td>{getStandardUnitLabel(measurementFamily, locale)}</td>
      <td>{measurementFamily.units.length}</td>
    </Container>
  );
});

export {MeasurementFamilyRow};
