import React, {FormEvent, useRef} from 'react';
import styled from 'styled-components';
import {MeasurementFamily, setMeasurementFamilyLabel} from '../../model/measurement-family';
import {SubsectionHeader} from '../../shared/components/Subsection';
import {TextField} from '../../shared/components/TextField';
import {useUiLocales} from '../../shared/hooks/use-ui-locales';
import {FormGroup} from '../../shared/components/FormGroup';
import {useAutoFocus, ValidationError, filterErrors} from '@akeneo-pim-community/shared';
import {useTranslate, useSecurity} from '@akeneo-pim-community/legacy';

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
  const __ = useTranslate();
  const locales = useUiLocales();
  const {isGranted} = useSecurity();

  const firstFieldRef = useRef<HTMLInputElement | null>(null);
  useAutoFocus(firstFieldRef);

  return (
    <Container>
      <SubsectionHeader top={0}>{__('pim_common.general_properties')}</SubsectionHeader>
      <FormGroup>
        <TextField
          id="measurements.family.properties.code"
          label={__('pim_common.code')}
          value={measurementFamily.code}
          errors={filterErrors(errors, 'code')}
          required={true}
          readOnly={true}
        />
      </FormGroup>
      <SubsectionHeader top={0}>{__('measurements.label_translations')}</SubsectionHeader>
      <FormGroup>
        {null !== locales &&
          locales.map((locale, index) => (
            <TextField
              ref={0 === index ? firstFieldRef : undefined}
              id={`measurements.family.properties.label.${locale.code}`}
              label={locale.label}
              errors={filterErrors(errors, `labels[${locale.code}]`)}
              key={locale.code}
              flag={locale.code}
              readOnly={!isGranted('akeneo_measurements_measurement_family_edit_properties')}
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
