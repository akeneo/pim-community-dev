import React from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, RulesIllustration} from 'akeneo-design-system';

const Content = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 80px 0;
  padding: 20px;
  gap: 10px;
`;

const Title = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
  text-align: center;
`;

type DeletedSourcePlaceholderProps = {
  message: string;
};

const DeletedSourcePlaceholder = ({message}: DeletedSourcePlaceholderProps) => {
  return (
    <Content>
      <RulesIllustration size={128} />
      <Title>{message}</Title>
    </Content>
  );
};

export {DeletedSourcePlaceholder};
