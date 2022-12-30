import React, {useCallback} from 'react';
import {FreeText} from '../../../models';
import {Field, TextInput, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {PropertyEditFieldsProps} from '../PropertyEdit';
import {useIdentifierGeneratorAclContext} from '../../../context';

const FreeTextEdit: PropertyEditFieldsProps<FreeText> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();
  const onTextChange = useCallback(
    (text: string) => {
      onChange({...selectedProperty, string: text});
    },
    [onChange, selectedProperty]
  );

  const stringInputRef = React.useRef<HTMLInputElement | null>(null);
  useAutoFocus(stringInputRef);

  return (
    <Field label={translate('pim_identifier_generator.structure.settings.free_text.string_label')}>
      <TextInput
        value={selectedProperty.string}
        onChange={onTextChange}
        maxLength={100}
        ref={stringInputRef}
        readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
      />
    </Field>
  );
};

export {FreeTextEdit};
