import React from 'react';
import styled from 'styled-components';
import {Theme as AkeneoTheme} from 'akeneo-design-system';

type Level = 'info' | 'warning' | 'error';

const getAnchorColor = (level: Level, theme: AkeneoTheme) => {
  switch (level) {
    case 'error':
      return theme.color.red100;
    case 'warning':
      return theme.color.yellow120;
    default:
      return theme.color.blue100;
  }
};

const getColor = (level: Level, theme: AkeneoTheme) => {
  switch (level) {
    case 'error':
      return theme.color.red100;
    case 'warning':
      return theme.color.yellow120;
    default:
      return theme.color.grey120;
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

const getIcon = (level: Level | undefined) => {
  switch (level) {
    case 'error':
      return '/bundles/pimui/images/icon-danger.svg';
    case 'warning':
      return '/bundles/pimui/images/icon-danger-orange.svg';
    default:
      return '/bundles/pimui/images/icon-info.svg';
  }
};

const SmallErrorHelper = styled.div<{level: Level}>`
  &:not(:empty) {
    display: flex;
    align-items: center;
    background: ${({theme, level}) => getBackgroundColor(level, theme)};
    min-height: 44px;
    padding: 10px;
    margin-bottom: 4px;
  }
`;

type Props = {
  level?: Level;
};

export const SmallHelperText = styled.span<{level: Level}>`
  align-items: center;
  border-left: 1px solid ${({theme, level}) => getBorderColor(level, theme)};
  color: ${({theme, level}) => getColor(level, theme)};
  display: flex;
  padding-left: 10px;
  a {
    color: ${({theme, level}) => getAnchorColor(level, theme)};
    cursor: pointer;
    text-decoration: underline;
  }
`;

const HelperImg = styled.img`
  height: 20px;
  padding-right: 10px;
`;

export const SmallHelper: React.FC<Props> = ({level = 'info', children}) => {
  if (
    (Array.isArray(children) && children.length) ||
    typeof children === 'string'
  ) {
    return (
      <SmallErrorHelper level={level}>
        <HelperImg alt={`icon-${level}`} src={getIcon(level)} />
        <SmallHelperText level={level}>{children}</SmallHelperText>
      </SmallErrorHelper>
    );
  }
  return null;
};
