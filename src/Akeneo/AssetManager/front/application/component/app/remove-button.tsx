import * as React from 'react';
import styled from 'styled-components';
import {TransparentButton} from 'akeneoassetmanager/application/component/app/button';
import Close from 'akeneoassetmanager/application/component/app/icon/close';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';

const Container = styled(TransparentButton)`
  width: 18px;
  height: 18px;
  flex-shrink: 0;
`;

export const RemoveButton = ({title, ...props}: {title: string} & any) => (
  <Container title={title} tabIndex={0} {...props}>
    <Close color={akeneoTheme.color.grey100} title={title} size={18} />
  </Container>
);
