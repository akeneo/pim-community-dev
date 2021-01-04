import React, {useRef} from 'react';
import styled from 'styled-components';
import {MeasurementFamily, setMeasurementFamilyLabel} from 'akeneomeasure/model/measurement-family';
import {SubsectionHeader} from 'akeneomeasure/shared/components/Subsection';
import {useUiLocales} from 'akeneomeasure/shared/hooks/use-ui-locales';
import {FormGroup} from 'akeneomeasure/shared/components/FormGroup';
import {useAutoFocus, ValidationError, filterErrors, TextField} from '@akeneo-pim-community/shared';
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

  const firstFieldRef = useRef<HTMLInputElement | null>(null);
  useAutoFocus(firstFieldRef);

  return (
    <Container>
      <SubsectionHeader top={0}>{translate('pim_common.general_properties')}</SubsectionHeader>
      <FormGroup>
        <TextField
          label={translate('pim_common.code')}
          required={true}
          value={measurementFamily.code}
          readOnly={true}
          errors={filterErrors(errors, 'code')}
        />
      </FormGroup>
      <SubsectionHeader top={0}>{translate('measurements.label_translations')}</SubsectionHeader>
      <FormGroup>
        {null !== locales &&
          locales.map((locale, index) => (
            <TextField
              key={locale.code}
              label={locale.label}
              locale={locale.code}
              ref={0 === index ? firstFieldRef : undefined}
              readOnly={!isGranted('akeneo_measurements_measurement_family_edit_properties')}
              value={measurementFamily.labels[locale.code] || ''}
              onChange={value =>
                onMeasurementFamilyChange(setMeasurementFamilyLabel(measurementFamily, locale.code, value))
              }
              errors={filterErrors(errors, `labels[${locale.code}]`)}
            />
          ))}
      </FormGroup>
    </Container>
  );
};

export {PropertyTab};
