import React, {ReactElement} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, Override} from 'akeneo-design-system';

const CenteredHelperContainer = styled.div`
  text-align: center;
  & > * {
    display: block;
    margin: auto;
  }
`;

const CenteredHelperModalContainer = styled.div`
  max-width: 258px;
  padding: 0 20px;
`;

const CenteredHelperTitle = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
`;

type CenteredHelperProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    illustration?: ReactElement;
  }
>;

const CenteredHelper = ({illustration, children, ...rest}: CenteredHelperProps) => {
  return (
    <CenteredHelperContainer {...rest}>
      {illustration && React.cloneElement(illustration, {size: 120})}
      {children}
    </CenteredHelperContainer>
  );
};

CenteredHelper.Title = CenteredHelperTitle;
CenteredHelper.Container = CenteredHelperModalContainer;
export {CenteredHelper};
