import React from 'react';
import styled from 'styled-components';
import {SectionTitle} from 'akeneo-design-system';
import {MeasurementFamily, setMeasurementFamilyLabel} from 'akeneomeasure/model/measurement-family';
import {useUiLocales} from 'akeneomeasure/shared/hooks/use-ui-locales';
import {ValidationError, filterErrors, TextField, Section} from '@akeneo-pim-community/shared';
import {useTranslate, useSecurity} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  flex: 1;
  overflow: auto;
`;

const PropertyTab = ({
  measurementFamily,
  errors,
  onMeasurementFamilyChange,
}: {
  measurementFamily: MeasurementFamily;
  errors: ValidationError[];
  onMeasurementFamilyChange: (measurementFamily: MeasurementFamily) => void;
}) => {
  const translate = useTranslate();
  const locales = useUiLocales();
  const {isGranted} = useSecurity();

  return (
    <Container>
      <Section>
        <SectionTitle sticky={0}>
          <SectionTitle.Title>{translate('pim_common.general_properties')}</SectionTitle.Title>
        </SectionTitle>
        <TextField
          label={translate('pim_common.code')}
          value={measurementFamily.code}
          errors={filterErrors(errors, 'code')}
          required={true}
          readOnly={true}
        />
        <SectionTitle sticky={0}>
          <SectionTitle.Title>{translate('measurements.label_translations')}</SectionTitle.Title>
        </SectionTitle>
        {null !== locales &&
          locales.map((locale, index) => (
            <TextField
              autoFocus={0 === index}
              label={locale.label}
              errors={filterErrors(errors, `labels[${locale.code}]`)}
              key={locale.code}
              locale={locale.code}
              readOnly={!isGranted('akeneo_measurements_measurement_family_edit_properties')}
              value={measurementFamily.labels[locale.code] || ''}
              onChange={value =>
                onMeasurementFamilyChange(setMeasurementFamilyLabel(measurementFamily, locale.code, value))
              }
            />
          ))}
      </Section>
    </Container>
  );
};

export {PropertyTab};
