import styled from 'styled-components';
import React, {PropsWithChildren} from 'react';
import {WarningIcon} from 'akeneomeasure/shared/icons/WarningIcon';
import {akeneoTheme} from 'akeneomeasure/shared/theme';

enum HelperLevel {
  HELPER_LEVEL_WARNING = 'warning',
  HELPER_LEVEL_INFO = 'info',
  HELPER_LEVEL_ERROR = 'error',
}

const getBackgroundColor = (level: HelperLevel): string => {
  switch (level) {
    case HelperLevel.HELPER_LEVEL_INFO:
      return akeneoTheme.color.blue10;
    case HelperLevel.HELPER_LEVEL_ERROR:
      return akeneoTheme.color.red10;
    case HelperLevel.HELPER_LEVEL_WARNING:
    default:
      return akeneoTheme.color.yellow10;
  }
};

const getForegroundColor = (level: HelperLevel): string => {
  switch (level) {
    case HelperLevel.HELPER_LEVEL_INFO:
      return akeneoTheme.color.blue100;
    case HelperLevel.HELPER_LEVEL_ERROR:
      return akeneoTheme.color.red100;
    case HelperLevel.HELPER_LEVEL_WARNING:
    default:
      return akeneoTheme.color.yellow120;
  }
};

const getIcon = (level: HelperLevel): JSX.Element => {
  switch (level) {
    case HelperLevel.HELPER_LEVEL_INFO:
      return <WarningIcon color={akeneoTheme.color.blue120} />;
    case HelperLevel.HELPER_LEVEL_ERROR:
      return <WarningIcon color={akeneoTheme.color.red120} />;
    case HelperLevel.HELPER_LEVEL_WARNING:
    default:
      return <WarningIcon color={akeneoTheme.color.yellow120} />;
  }
};

const HelperRibbonContainer = styled.div<{level: HelperLevel}>`
  align-items: center;
  background: ${props => getBackgroundColor(props.level)};
  color: ${props => getForegroundColor(props.level)};
  display: flex;
  font-weight: 600;
  margin-bottom: 1px;
  padding-right: 15px;
`;

const HelperRibbonIconContainer = styled.div<{level: HelperLevel}>`
  padding: 12px;
  position: relative;
  display: flex;
  margin: 0 15px 0 0;

  &:after {
    background-color: ${props => getForegroundColor(props.level)};
    content: '';
    display: block;
    height: 24px;
    margin-top: -12px;
    position: absolute;
    right: 0;
    top: 50%;
    width: 1px;
  }
`;

const HelperRibbonIcon = ({level}: {level: HelperLevel}) => (
  <HelperRibbonIconContainer level={level}>{getIcon(level)}</HelperRibbonIconContainer>
);

type HelperRibbonProps = {
  level: HelperLevel;
};

const HelperRibbon = ({level, children}: PropsWithChildren<HelperRibbonProps>) => (
  <HelperRibbonContainer level={level}>
    <HelperRibbonIcon level={level} />
    {children}
  </HelperRibbonContainer>
);

export {HelperLevel, HelperRibbon};
