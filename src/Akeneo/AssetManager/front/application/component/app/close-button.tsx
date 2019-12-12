import * as React from 'react';
import styled from 'styled-components';
import {TransparentButton} from 'akeneoassetmanager/application/component/app/button';
import Close from 'akeneoassetmanager/application/component/app/icon/close';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';

const Container = styled(TransparentButton)`
  width: 24px;
  height: 24px;
  position: absolute;
  top: 0;
  left: 0;
`;

export const CloseButton = ({title, ...props}: {title: string} & any) => (
  <Container title={title} tabIndex={0} aria-label={title} {...props}>
    <Close color={akeneoTheme.color.grey100} title={title} size={24} />
  </Container>
);
