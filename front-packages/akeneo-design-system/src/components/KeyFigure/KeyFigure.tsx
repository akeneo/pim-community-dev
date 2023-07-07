import React, {isValidElement, ReactElement, ReactNode} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from '../../theme';
import type {IconProps} from '../../icons/IconProps';

type FigureProps = {
  label?: string;
  children: ReactNode;
};

const FigureContainer = styled.div`
  color: ${getColor('brand', 100)};
  font-size: 16px;
  margin: 0 15px 0 3px;

  :only-child {
    margin: 0;
  }
`;

const Figure = ({label, children}: FigureProps) => {
  return (
    <>
      {label && `${label} `}
      <FigureContainer>{children}</FigureContainer>
    </>
  );
};

type KeyFigureProps = {
  icon: ReactElement<IconProps>;
  title: string;
  children?: ReactNode;
};

const KeyFigureContainer = styled.div`
  height: 80px;
  display: inline-flex;
  box-sizing: border-box;
  background: ${getColor('white')};
`;

const IconContainer = styled.div`
  min-width: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 1px ${getColor('grey', 80)} solid;
  margin: 10px 0;

  svg {
    color: ${getColor('grey', 100)};
  }
`;

const ContentContainer = styled.div`
  margin: 15px 20px;
  display: flex;
  justify-content: space-around;
  flex-direction: column;
  min-width: 0;
`;

const Title = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
`;
const Values = styled.div`
  display: flex;
  color: ${getColor('grey', 100)};
  font-size: 16px;
`;

const KeyFigure = ({icon, title, children, ...props}: KeyFigureProps) => {
  const validIcon = isValidElement<IconProps>(icon) && React.cloneElement(icon, {size: 30});

  return (
    <KeyFigureContainer {...props}>
      <IconContainer>{validIcon}</IconContainer>
      <ContentContainer>
        <Title>{title}</Title>
        <Values>{children}</Values>
      </ContentContainer>
    </KeyFigureContainer>
  );
};

const KeyFigureGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 20px;
`;

KeyFigure.Figure = Figure;

export {KeyFigure, KeyFigureGrid};
