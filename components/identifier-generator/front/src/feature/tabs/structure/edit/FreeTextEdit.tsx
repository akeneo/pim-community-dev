import React, {useCallback, useMemo} from 'react';
import {FreeText} from '../../../models';
import {Field, TextInput, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {PropertyEditFieldsProps} from '../PropertyEdit';

const LIMIT = 100;

const FreeTextEdit: PropertyEditFieldsProps<FreeText> = ({selectedProperty, onChange}) => {
  const translate = useTranslate();
  const onTextChange = useCallback(
    (text: string) => {
      onChange({...selectedProperty, string: text});
    },
    [onChange, selectedProperty]
  );
  const characterLeftLabel = useMemo(
    () =>
      translate(
        'pim_common.characters_left',
        {count: LIMIT - selectedProperty.string.length},
        LIMIT - selectedProperty.string.length
      ),
    [selectedProperty.string.length, translate]
  );
  const stringInputRef = React.useRef<HTMLInputElement | null>(null);

  useAutoFocus(stringInputRef);

  return (
    <Field label={translate('pim_identifier_generator.structure.settings.free_text.string_label')}>
      <TextInput
        value={selectedProperty.string}
        onChange={onTextChange}
        maxLength={LIMIT}
        ref={stringInputRef}
        characterLeftLabel={characterLeftLabel}
      />
    </Field>
  );
};

export {FreeTextEdit};
