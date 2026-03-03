import styled from 'styled-components';
import {getColor} from '../../../theme';
import React from 'react';
import {ItemLabel} from '../Item/Item';

const SurtitleContainer = styled.div`
  display: flex;
  flex-direction: column;
  justify-content: center;
  flex-grow: 1;
  line-height: normal;
  overflow: hidden;
`;

const Title = styled.span`
  color: ${getColor('grey', 100)};
  font-size: 10px;
  overflow: hidden;
  text-overflow: ellipsis;
`;

type SurtitleProps = {label: string; children?: React.ReactNode};

const Surtitle: React.FC<SurtitleProps> = ({label, children, ...rest}) => (
  <SurtitleContainer {...rest}>
    <Title title={label}>{label}</Title>
    <ItemLabel>{children}</ItemLabel>
  </SurtitleContainer>
);

export {Surtitle};
