import React from 'react';
import styled from 'styled-components';
import {TransparentButton} from 'akeneoassetmanager/application/component/app/button';
import {CloseIcon, getColor} from 'akeneo-design-system';

const Container = styled(TransparentButton)`
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  color: ${getColor('grey', 100)};
`;

//TODO RAC-414 use DSM IconButton
export const RemoveButton = ({title, ...props}: {title: string} & any) => (
  <Container title={title} tabIndex={0} {...props}>
    <CloseIcon size={18} />
  </Container>
);
