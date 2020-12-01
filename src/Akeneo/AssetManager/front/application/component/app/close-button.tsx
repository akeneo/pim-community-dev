import React from 'react';
import styled from 'styled-components';
import {TransparentButton} from 'akeneoassetmanager/application/component/app/button';
import {CloseIcon, getColor} from 'akeneo-design-system';

const Container = styled(TransparentButton)`
  width: 24px;
  height: 24px;
  position: absolute;
  top: 0;
  left: 0;
  color: ${getColor('grey', 100)};
`;

//TODO RAC-414 use DSM IconButton
export const CloseButton = ({title, ...props}: {title: string} & any) => (
  <Container title={title} tabIndex={0} aria-label={title} {...props}>
    <CloseIcon size={24} />
  </Container>
);
