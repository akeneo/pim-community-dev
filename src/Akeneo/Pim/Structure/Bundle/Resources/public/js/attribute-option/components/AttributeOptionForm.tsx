import React, {FC, useCallback, useEffect, useRef} from 'react';

import {AttributeOption, Locale} from '../model';
import {useEditingOptionContext} from '../contexts';
import {Field, TextInput} from 'akeneo-design-system';
import styled from 'styled-components';

type AttributeOptionFormProps = {
  option: AttributeOption;
  locale: Locale;
  onUpdateOptionLabel: (newLabel: string, localeCode: string) => void;
};

const AttributeOptionForm: FC<AttributeOptionFormProps> = ({option, locale, onUpdateOptionLabel}) => {
  const inputRef = useRef<HTMLInputElement>(null);
  const {addRef, removeRef} = useEditingOptionContext();

  const handleOnChange = useCallback(newLabel => onUpdateOptionLabel(newLabel, locale.code), []);

  // In order to be able to apply the spellcheck on EE, we share the reference of the input field for the locale
  // To fix the PIM-10622 issue on Firefox, we listen the changes on the reference input field to ensure to apply them on the state.
  useEffect(() => {
    addRef(locale.code, inputRef);
    const handleChangeRef = (event: any) => {
      if (!event.target || event.target.value === undefined) {
        return;
      }
      handleOnChange(event.target.value);
    };
    if (inputRef.current !== null) {
      inputRef.current.addEventListener('change', handleChangeRef);
    }

    return () => {
      removeRef(locale.code, inputRef);

      if (inputRef.current !== null) {
        inputRef.current.removeEventListener('change', handleChangeRef);
      }
    };
  }, [locale, addRef, removeRef]);

  return (
    <Container>
      <Field label={locale.label} locale={locale.code}>
        <TextInputStyled
          ref={inputRef}
          value={option.optionValues[locale.code].value ?? ''}
          onChange={handleOnChange}
          data-locale={locale.code}
          data-testid="attribute-option-label"
        />
      </Field>
    </Container>
  );
};

const Container = styled.div`
  margin-bottom: 20px;
`;

const TextInputStyled = styled(TextInput)`
  padding: 0 9px;
`;

export default AttributeOptionForm;
