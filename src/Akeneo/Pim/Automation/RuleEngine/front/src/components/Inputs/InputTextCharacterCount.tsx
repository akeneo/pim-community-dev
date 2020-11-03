import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../dependenciesTools/hooks';
import {useFormContext} from 'react-hook-form';

const StyledComponent = styled.span`
  float: right;
  font-size: 11px;
  text-align: right;
  color: ${({theme}): string => theme.color.grey100};
  margin-top: 5px;
  line-height: 15px;
  margin-left: 20px;
`;

type Props = {
  formName: string;
  maxLength: number;
};

const InputTextCharacterCount: React.FC<Props> = ({formName, maxLength}) => {
  const translate = useTranslate();
  const [leftCharacters, setLeftCharacters] = React.useState<number>(maxLength);
  const {watch} = useFormContext();

  const getFormValueLength = (): number => {
    const value = watch(formName);

    return 'string' === typeof value ? value.length : 0;
  };

  React.useEffect(() => {
    setLeftCharacters(Math.max(0, maxLength - getFormValueLength()));
  }, [getFormValueLength()]);

  return (
    <StyledComponent>
      {translate(
        'pimee_catalog_rule.form.edit.character_left',
        {
          count: leftCharacters,
        },
        leftCharacters
      )}
    </StyledComponent>
  );
};

export {InputTextCharacterCount};
