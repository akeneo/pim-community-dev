import React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const StyledButton = styled(Button)`
  margin-right: 10px;
`;

type Props = {
  onClick: () => void;
  disabled: boolean;
};

const CompareTranslateButton: React.FC<Props> = ({onClick, disabled}) => {
  const translate = useTranslate();

  return (
    <StyledButton ghost level="secondary" onClick={onClick} disabled={disabled}>
      {translate('free_trial.product-edit-form.compare_translate.button_title')}
    </StyledButton>
  );
};

export {CompareTranslateButton};
