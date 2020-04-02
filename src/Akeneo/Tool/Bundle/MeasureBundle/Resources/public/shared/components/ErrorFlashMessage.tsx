import React, {PropsWithChildren} from 'react';
import styled from 'styled-components';
import {WarningIcon} from 'akeneomeasure/shared/icons/WarningIcon';
import {akeneoTheme} from 'akeneomeasure/shared/theme';

const ErrorFlashMessageContainer = styled.div`
  align-items: center;
  background: ${props => props.theme.color.red10};
  color: ${props => props.theme.color.red100};
  display: flex;
  font-weight: 600;
  margin-bottom: 1px;
`;

const ErrorFlashMessageIconContainer = styled.div`
  padding: 12px;
  position: relative;
  display: flex;
  margin: 0 15px 0 0;

  &:after {
    background-color: ${props => props.theme.color.red100};
    content: '';
    display: block;
    height: 24px;
    margin-top: -12px;
    position: absolute;
    right: 0;
    top: 50%;
    width: 1px;
  }
`;

const ErrorFlashMessage = ({children}: PropsWithChildren<{}>) => (
  <ErrorFlashMessageContainer>
    <ErrorFlashMessageIconContainer>
      <WarningIcon color={akeneoTheme.color.red120} />
    </ErrorFlashMessageIconContainer>
    {children}
  </ErrorFlashMessageContainer>
);

export {ErrorFlashMessage};
