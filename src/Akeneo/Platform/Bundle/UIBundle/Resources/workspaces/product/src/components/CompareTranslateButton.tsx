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
      {translate('pim_enrich.entity.product.module.copy.button_label')}
    </StyledButton>
  );
};

export {CompareTranslateButton};
