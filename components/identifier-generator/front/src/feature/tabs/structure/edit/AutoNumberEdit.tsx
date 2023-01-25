import React, {useCallback} from 'react';
import {AutoNumber} from '../../../models';
import {Field, NumberInput, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {PropertyEditFieldsProps} from '../PropertyEdit';
import {useIdentifierGeneratorAclContext} from '../../../context';

const AutoNumberEdit: PropertyEditFieldsProps<AutoNumber> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();
  const onDigitsMinChange = useCallback(
    (value: string) => {
      onChange({...selectedProperty, digitsMin: Math.max(1, Number(value))});
    },
    [onChange, selectedProperty]
  );

  const onNumberMinChange = useCallback(
    (value: string) => {
      onChange({...selectedProperty, numberMin: Number(value)});
    },
    [onChange, selectedProperty]
  );

  const digitsMinInputRef = React.useRef<HTMLInputElement | null>(null);
  useAutoFocus(digitsMinInputRef);

  return (
    <>
      <Field label={translate('pim_identifier_generator.structure.settings.auto_number.digitsMin_label')}>
        <NumberInput
          value={`${selectedProperty.digitsMin}`}
          onChange={onDigitsMinChange}
          min={1}
          max={15}
          ref={digitsMinInputRef}
          readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
        />
      </Field>
      <Field label={translate('pim_identifier_generator.structure.settings.auto_number.numberMin_label')}>
        <NumberInput
          value={`${selectedProperty.numberMin}`}
          onChange={onNumberMinChange}
          min={0}
          readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
        />
      </Field>
    </>
  );
};

export {AutoNumberEdit};
