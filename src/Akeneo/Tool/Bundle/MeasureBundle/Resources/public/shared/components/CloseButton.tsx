import * as React from 'react';
import styled from 'styled-components';
import {CloseIcon} from 'akeneomeasure/shared/icons/CloseIcon';
import {akeneoTheme} from 'akeneomeasure/shared/theme';

const Container = styled.button`
  background: none;
  border: none;
  height: 24px;
  left: 0;
  margin: 0;
  padding: 0;
  position: absolute;
  top: 0;
  width: 24px;

  &:hover {
    cursor: pointer;
  }
`;

export const CloseButton = ({title, ...props}: {title: string} & any) => (
  <Container title={title} tabIndex={0} aria-label={title} {...props}>
    <CloseIcon color={akeneoTheme.color.grey100} title={title} size={24} />
  </Container>
);
