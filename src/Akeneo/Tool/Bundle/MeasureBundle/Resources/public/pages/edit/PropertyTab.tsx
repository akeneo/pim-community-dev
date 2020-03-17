import React, {useContext, FormEvent} from 'react';
import styled from 'styled-components';
import {MeasurementFamily, setMeasurementFamilyLabel} from 'akeneomeasure/model/measurement-family';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {SubsectionHeader} from 'akeneomeasure/shared/components/Subsection';
import {TextField} from 'akeneomeasure/shared/components/TextField';
import {useUiLocales} from 'akeneomeasure/shared/hooks/use-ui-locales';
import {FormGroup} from 'akeneomeasure/shared/components/FormGroup';
import {useFocus} from 'akeneomeasure/shared/hooks/use-focus';

const Container = styled.div``;

const PropertyTab = ({
  measurementFamily,
  onMeasurementFamilyChange,
}: {
  measurementFamily: MeasurementFamily;
  onMeasurementFamilyChange: (measurementFamily: MeasurementFamily) => void;
}) => {
  const __ = useContext(TranslateContext);
  const locales = useUiLocales();
  //TODO check why does not work
  const [ref] = useFocus();

  console.log(ref);

  return (
    <Container>
      <SubsectionHeader>{__('pim_common.general_properties')}</SubsectionHeader>
      <FormGroup>
        <TextField
          id="measurements.family.properties.code"
          label={__('pim_common.code')}
          value={measurementFamily.code}
          required
          readOnly
        />
      </FormGroup>
      <SubsectionHeader>{__('pim_common.label_translations')}</SubsectionHeader>
      <FormGroup>
        {null !== locales &&
          locales.map((locale, index) => (
            <TextField
              ref={0 === index ? ref : undefined}
              id={`measurements.family.properties.label.${locale.code}`}
              label={locale.label}
              key={locale.code}
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
