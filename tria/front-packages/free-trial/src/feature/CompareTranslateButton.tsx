import React from 'react';
import {Button} from "akeneo-design-system";
import {useTranslate} from "@akeneo-pim-community/shared";
import styled from "styled-components";

const StyledButton = styled(Button)`
  margin-right: 10px;
`;

type Props = {
  onClick: () => void;
};

const CompareTranslateButton: React.FC<Props> = ({onClick}) => {
  const translate = useTranslate();

  return (
    <StyledButton ghost level="secondary" onClick={onClick}>
      {translate('free_trial.product-edit-form.compare_translate.button_title')}
    </StyledButton>
  );
};

export {CompareTranslateButton};
