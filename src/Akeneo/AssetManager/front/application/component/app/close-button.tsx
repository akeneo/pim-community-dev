import React from 'react';
import styled from 'styled-components';
import {IconButtonProps, CloseIcon, IconButton} from 'akeneo-design-system';

const Container = styled(IconButton)`
  position: absolute;
  top: 0;
  left: 0;
`;

const CloseButton = (props: Omit<IconButtonProps, 'icon'>) => (
  <Container tabIndex={0} {...props} level="tertiary" ghost="borderless" icon={<CloseIcon />} />
);

export {CloseButton};
