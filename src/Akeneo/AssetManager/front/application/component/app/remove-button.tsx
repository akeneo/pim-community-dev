import * as React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import {TransparentButton} from 'akeneoassetmanager/application/component/app/button';
import Key from 'akeneoassetmanager/tools/key';
import Close from 'akeneoassetmanager/application/component/app/icon/close';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';

const Container = styled(TransparentButton)`
  width: 22px;
  height: 22px;
`;

export const RemoveButton = ({title, onAction}: {title: string; onAction: () => void}) => {
  return (
    <Container
      title={title}
      tabIndex={0}
      onClick={onAction}
      onKeyPress={(event: React.KeyboardEvent<HTMLButtonElement>) => {
        if (Key.Space === event.key) onAction();
      }}
    >
      <Close color={akeneoTheme.color.grey100} title={title} />
    </Container>
  );
};
