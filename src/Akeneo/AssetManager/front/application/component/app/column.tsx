import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

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

export const Column = styled.div`
  padding: 30px;
  flex-basis: 280px;
  width: 280px;
  position: relative;
  transition: flex-basis 0.3s ease-in-out, width 0.3s ease-in-out;
  order: -10;
  background: ${(props: ThemedProps<void>) => props.theme.color.grey60};
  border-right: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  flex-shrink: 0;
  height: 100%;
  z-index: 802;
  overflow: hidden;
`;
