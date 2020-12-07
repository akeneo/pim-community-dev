import * as React from 'react';
import styled, {css} from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {useStoredState} from 'akeneoassetmanager/application/hooks/state';

export const ColumnTitle = styled.div`
  display: block;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey100};
  text-transform: uppercase;
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
  white-space: nowrap;
  margin-bottom: 3px;

  :not(:first-child) {
    margin-top: 30px;
  }
`;

const InnerColumn = styled.div`
  width: 280px;
  height: calc(100% - 70px);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: absolute;
  right: 0;
  overflow-x: auto;
  transition: right 0.3s ease-in-out;
`;

const CollapseButton = styled.div`
  height: 70px;
  width: 280px;
  background: url(/bundles/pimui/images/icon-panelClose.svg) no-repeat 20px center;
  background-size: 30px;
  background-color: ${(props: ThemedProps<void>) => props.theme.color.grey20};
  cursor: pointer;
  opacity: 0.8;
  position: absolute;
  bottom: 0;
  right: 0;
  border-top: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  transition: opacity 0.2s ease-in-out, width 0.3s ease-in-out, background-position 0.3s ease-in-out;

  :hover {
    opacity: 1;
  }
`;

const ColumnContent = styled.div`
  padding: 30px;
  transition: margin 0.3s ease-in-out;
`;

const ColumnContainer = styled.div<{isCollapsed: boolean}>`
  flex-basis: 280px;
  width: 280px;
  position: relative;
  transition: flex-basis 0.3s ease-in-out, width 0.3s ease-in-out;
  order: -10;
  background-color: ${(props: ThemedProps<{isCollapsed: boolean}>) => props.theme.color.grey20};
  border-right: 1px solid ${(props: ThemedProps<{isCollapsed: boolean}>) => props.theme.color.grey80};
  flex-shrink: 0;
  height: 100%;
  z-index: 802;
  overflow: hidden;

  ${props =>
    props.isCollapsed &&
    css`
      flex-basis: 40px;
      width: 40px;

      ${CollapseButton} {
        width: 40px;
        background-position: 5px center;
        background-image: url(/bundles/pimui/images/icon-panelOpen.svg);
      }

      ${ColumnContent} {
        margin-left: -280px;
      }
    `}
`;

type ColumnProps = {
  title: string;
  children: React.ReactElement[];
};

export const Column = ({title, children}: ColumnProps) => {
  const [isCollapsed, setCollapsed] = useStoredState<boolean>('collapsedColumn_pim-menu-asset_manager', false);

  return (
    <ColumnContainer isCollapsed={isCollapsed}>
      <InnerColumn>
        <ColumnContent>
          <ColumnTitle>{title}</ColumnTitle>
          {children}
        </ColumnContent>
      </InnerColumn>
      <CollapseButton onClick={() => setCollapsed(!isCollapsed)} />
    </ColumnContainer>
  );
};
