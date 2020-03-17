import React, {useContext, FormEvent} from 'react';
import styled from 'styled-components';
import {MeasurementFamily, setMeasurementFamilyLabel} from 'akeneomeasure/model/measurement-family';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {SubsectionHeader} from 'akeneomeasure/shared/components/Subsection';
import {TextField} from 'akeneomeasure/shared/components/TextField';

const Container = styled.div``;

const PropertyTab = ({
  measurementFamily,
  onMeasurementFamilyChange,
}: {
  measurementFamily: MeasurementFamily;
  onMeasurementFamilyChange: (measurementFamily: MeasurementFamily) => void;
}) => {
  const __ = useContext(TranslateContext);

  return (
    <Container>
      <SubsectionHeader>{__('measurements.family.properties.general_properties')}</SubsectionHeader>
      <TextField
        id="measurements.family.properties.code"
        label={__('measurements.form.input.code')}
        value={measurementFamily.code}
        required
        readOnly
      />
      <SubsectionHeader>{__('measurements.family.properties.label_translations')}</SubsectionHeader>
      {Object.keys(measurementFamily.labels).map(locale => (
        <TextField
          id={`measurements.family.properties.label.${locale}`}
          label={locale}
          key={locale}
          value={measurementFamily.labels[locale]}
          onChange={(event: FormEvent<HTMLInputElement>) =>
            onMeasurementFamilyChange(setMeasurementFamilyLabel(measurementFamily, locale, event.currentTarget.value))
          }
        />
      ))}
    </Container>
  );
};

export {PropertyTab};
