import React, {FC, useEffect, useRef} from 'react';

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

  useEffect(() => {
    addRef(locale.code, inputRef);

    return () => {
      removeRef(locale.code, inputRef);
    };
  }, [inputRef, addRef]);

  return (
    <Container>
      <Field label={locale.label} locale={locale.code}>
        <TextInputStyled
          ref={inputRef}
          value={option.optionValues[locale.code].value ?? ''}
          onChange={newLabel => onUpdateOptionLabel(newLabel, locale.code)}
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
