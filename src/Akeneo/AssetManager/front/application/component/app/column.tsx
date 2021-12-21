import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, SubNavigationPanel} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useStoredState} from 'akeneoassetmanager/application/hooks/state';

const ColumnTitle = styled.div`
  color: ${getColor('grey', 100)};
  text-transform: uppercase;
  font-size: ${getFontSize('small')};
  white-space: nowrap;
  margin-bottom: 3px;

  :not(:first-child) {
    margin-top: 30px;
  }
`;

type ColumnProps = {
  title: string;
  children: ReactNode;
};

const Column = ({title, children}: ColumnProps) => {
  const [isCollapsed, setCollapsed] = useStoredState<boolean>('collapsedColumn_pim-menu-asset_manager', false);
  const translate = useTranslate();

  return (
    <SubNavigationPanel
      isOpen={!isCollapsed}
      open={() => setCollapsed(false)}
      close={() => setCollapsed(true)}
      closeTitle={translate('pim_common.close')}
      openTitle={translate('pim_common.open')}
    >
      <ColumnTitle>{title}</ColumnTitle>
      {children}
    </SubNavigationPanel>
  );
};

export {Column, ColumnTitle};
