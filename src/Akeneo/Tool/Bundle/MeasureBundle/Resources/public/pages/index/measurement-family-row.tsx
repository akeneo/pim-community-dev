import React, {useContext} from 'react';
import styled from 'styled-components';
import {akeneoTheme} from 'akeneomeasure/shared/theme';
import {
  MeasurementFamily,
  getMeasurementFamilyLabel,
  getStandardUnitLabel,
} from 'akeneomeasure/model/measurement-family';
import {UserContext} from 'akeneomeasure/context/user-context';

const Container = styled.tr`
  height: 54px;
  border-bottom: 1px solid ${akeneoTheme.color.grey70};
`;
const MeasurementFamilyLabelCell = styled.td`
  color: ${akeneoTheme.color.purple100};
  font-style: italic;
  font-weight: bold;
`;

type MeasurementFamilyRowProps = {
  measurementFamily: MeasurementFamily;
};

export const MeasurementFamilyRow = ({measurementFamily}: MeasurementFamilyRowProps) => {
  const locale = useContext(UserContext)('uiLocale');

  return (
    <Container>
      <MeasurementFamilyLabelCell>{getMeasurementFamilyLabel(measurementFamily, locale)}</MeasurementFamilyLabelCell>
      <td>{measurementFamily.code}</td>
      <td>{getStandardUnitLabel(measurementFamily, locale)}</td>
      <td>{measurementFamily.units.length}</td>
    </Container>
  );
};
