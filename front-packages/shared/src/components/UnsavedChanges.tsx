import React from 'react';
import {useTranslate} from '../hooks';
import styled from "styled-components";
import {getColor, DangerPlainIcon, useTheme} from "akeneo-design-system";

const UnsavedChangesContainer = styled.div`
  margin-top: 10px;
  line-height: 16px;
  color: ${getColor('grey140')};
  font-style: italic;
  border-bottom: 1px solid ${getColor('yellow100')};
  white-space: nowrap;
  display: flex;
  align-items: center;
`;

const UnsavedChangesIcon = styled(DangerPlainIcon)`
  margin-top: -2px;
`;

const UnsavedChanges = () => {
  const translate = useTranslate();
  const theme = useTheme();

  return (
    <UnsavedChangesContainer>
      <UnsavedChangesIcon color={theme.color.yellow100} size={18}/>
      <span>{translate('pim_common.entity_updated')}</span>
    </UnsavedChangesContainer>
  );
};

export {UnsavedChanges};
