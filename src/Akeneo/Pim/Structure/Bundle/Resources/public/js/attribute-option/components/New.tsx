import React, {useEffect, useRef} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Field, TextInput} from 'akeneo-design-system';
import styled from 'styled-components';

interface NewProps {
  createAttributeOption: (optionCode: string) => void;
}

const New = ({createAttributeOption}: NewProps) => {
  const translate = useTranslate();
  const newOptionCodeRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (newOptionCodeRef !== null && newOptionCodeRef.current) {
      newOptionCodeRef.current.focus();
    }
  }, []);

  const createNewOptionFromCode = (event: any) => {
    event.preventDefault();
    if (newOptionCodeRef.current !== null && newOptionCodeRef.current.value) {
      createAttributeOption(newOptionCodeRef.current.value.trim());
    }
  };

  return (
    <div className="AknSubsection AknAttributeOption-edit">
      <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
        <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_settings')}</span>
      </div>
      <form onSubmit={(event: any) => createNewOptionFromCode(event)}>
        <Container>
          <Field label={translate('pim_enrich.entity.attribute_option.module.edit.option_code')}>
            <TextInputStyled ref={newOptionCodeRef} data-testid="attribute-option-label" />
          </Field>
        </Container>
        <button className="AknButton AknButton--apply save" role="create-option-button" type="submit">
          {translate('pim_common.done')}
        </button>
      </form>
    </div>
  );
};

const Container = styled.div`
  margin-bottom: 20px;
`;

const TextInputStyled = styled(TextInput)`
  padding: 0 9px;
`;

export default New;
