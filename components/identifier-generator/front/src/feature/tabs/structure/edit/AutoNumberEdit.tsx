import React, {useCallback} from 'react';
import {AutoNumber} from '../../../models';
import {Field, NumberInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {PropertyEditFieldsProps} from '../PropertyEdit';

const AutoNumberEdit: PropertyEditFieldsProps<AutoNumber> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();
  const onDigitsMinChange = useCallback(
    (value: string) => {
      onChange({...selectedProperty, digitsMin: Number(value)});
    },
    [onChange, selectedProperty]
  );

  const onNumberMinChange = useCallback(
    (value: string) => {
      onChange({...selectedProperty, numberMin: Number(value)});
    },
    [onChange, selectedProperty]
  );

  return (
    <>
      <Field label={translate('pim_identifier_generator.structure.settings.auto_number.digitsMin_label')}>
        <NumberInput value={`${selectedProperty.digitsMin}`} onChange={onDigitsMinChange} min={1} max={15} />
      </Field>
      <Field label={translate('pim_identifier_generator.structure.settings.auto_number.numberMin_label')}>
        <NumberInput value={`${selectedProperty.numberMin}`} onChange={onNumberMinChange} min={0} />
      </Field>
    </>
  );
};

export {AutoNumberEdit};
