import React from 'react';
import styled from 'styled-components';
import { AkeneoTheme } from '@akeneo-pim-community/shared/src';

type Level = 'info' | 'warning' | 'error';

const getColor = (level: Level, theme: AkeneoTheme) => {
  switch (level) {
    case 'error':
      return theme.color.red100;
    case 'warning':
      return theme.color.yellow120;
    default:
      return theme.color.blue120;
  }
};

const getBorderColor = (level: Level, theme: AkeneoTheme) => {
  switch (level) {
    case 'error':
      return theme.color.red100;
    case 'warning':
      return theme.color.yellow120;
    default:
      return theme.color.grey80;
  }
};

const getBackgroundColor = (level: Level, theme: AkeneoTheme) => {
  switch (level) {
    case 'error':
      return theme.color.red10;
    case 'warning':
      return theme.color.yellow10;
    default:
      return theme.color.blue10;
  }
};

const getIcon = (level: Level) => {
  switch (level) {
    case 'error':
      return '/bundles/pimui/images/icon-danger.svg';
    case 'warning':
      return '/bundles/pimui/images/icon-danger-orange.svg';
    default:
      return '/bundles/pimui/images/icon-info.svg';
  }
};

const SmallErrorHelper = styled.ul<{ level: Level }>`
  &:not(:empty) {
    color: ${({ theme, level }) => getColor(level, theme)};
    background: ${({ theme, level }) => getBackgroundColor(level, theme)};
    min-height: 44px;
    padding: 10px;
    flex-basis: 100%;
    line-height: 20px;
    background-image: url('${({ level }) => getIcon(level)}');
    background-repeat: no-repeat;
    background-size: 20px;
    background-position: 10px 10px;
    padding-left: 52px;

    &:before {
      content: '';
      border-left: 1px solid ${({ theme, level }) =>
        getBorderColor(level, theme)};
      position: absolute;
      height: 20px;
      margin-left: -14px;
    }

    a {
      color: ${({ theme, level }) => getColor(level, theme)};
      cursor: pointer;
      text-decoration: underline;
    }
  }
`;

type Props = {
  level?: Level;
};

export const SmallHelper: React.FC<Props> = ({ level, children }) => {
  const levelOrDefault = level ?? 'info';

  return <SmallErrorHelper level={levelOrDefault}>{children}</SmallErrorHelper>;
};
