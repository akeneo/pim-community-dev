import React, {useContext, FormEvent} from 'react';
import styled from 'styled-components';
import {MeasurementFamily, setMeasurementFamilyLabel} from 'akeneomeasure/model/measurement-family';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {SubsectionHeader} from 'akeneomeasure/shared/components/Subsection';
import {TextField} from 'akeneomeasure/shared/components/TextField';
import {useUiLocales} from 'akeneomeasure/shared/hooks/use-ui-locales';
import {FormGroup} from 'akeneomeasure/shared/components/FormGroup';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  flex: 1;
  overflow: auto;
`;

const PropertyTab = ({
  measurementFamily,
  onMeasurementFamilyChange,
}: {
  measurementFamily: MeasurementFamily;
  onMeasurementFamilyChange: (measurementFamily: MeasurementFamily) => void;
}) => {
  const __ = useContext(TranslateContext);
  const locales = useUiLocales();

  return (
    <Container>
      <SubsectionHeader top={0}>{__('pim_common.general_properties')}</SubsectionHeader>
      <FormGroup>
        <TextField
          id="measurements.family.properties.code"
          label={__('pim_common.code')}
          value={measurementFamily.code}
          required={true}
          readOnly={true}
        />
      </FormGroup>
      <SubsectionHeader top={0}>{__('measurements.label_translations')}</SubsectionHeader>
      <FormGroup>
        {null !== locales &&
          locales.map((locale, index) => (
            <TextField
              autofocus={0 === index}
              id={`measurements.family.properties.label.${locale.code}`}
              label={locale.label}
              key={locale.code}
              flag={locale.code}
              value={measurementFamily.labels[locale.code] || ''}
              onChange={(event: FormEvent<HTMLInputElement>) =>
                onMeasurementFamilyChange(
                  setMeasurementFamilyLabel(measurementFamily, locale.code, event.currentTarget.value)
                )
              }
            />
          ))}
      </FormGroup>
    </Container>
  );
};

export {PropertyTab};
