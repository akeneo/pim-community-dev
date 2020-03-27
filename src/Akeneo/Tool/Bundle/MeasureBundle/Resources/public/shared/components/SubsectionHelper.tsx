import styled from 'styled-components';
import React, {PropsWithChildren} from 'react';
import {WarningIcon} from 'akeneomeasure/shared/icons/WarningIcon';
import {akeneoTheme} from 'akeneomeasure/shared/theme';

const HELPER_LEVEL_WARNING = 'warning';
const HELPER_LEVEL_INFO = 'info'; // @todo

type Level = typeof HELPER_LEVEL_WARNING | typeof HELPER_LEVEL_INFO;

const getBackgroundColor = (level: Level): string => {
  switch (level) {
    case HELPER_LEVEL_WARNING:
    default:
      return akeneoTheme.color.yellow10;
  }
};

const getForegroundColor = (level: Level): string => {
  switch (level) {
    case HELPER_LEVEL_WARNING:
    default:
      return akeneoTheme.color.yellow120;
  }
};

const getIcon = (level: Level): JSX.Element => {
  switch (level) {
    case HELPER_LEVEL_WARNING:
    default:
      return <WarningIcon color={akeneoTheme.color.yellow120} />;
  }
};

const SubsectionHelperContainer = styled.div<{level: Level}>`
  align-items: center;
  background: ${props => getBackgroundColor(props.level)};
  color: ${props => getForegroundColor(props.level)};
  display: flex;
  font-weight: 600;
  margin-bottom: 1px;
`;

const SubsectionHelperIconContainer = styled.div<{level: Level}>`
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

const SubsectionHelperIcon = ({level}: {level: Level}) => (
  <SubsectionHelperIconContainer level={level}>{getIcon(level)}</SubsectionHelperIconContainer>
);

type SubsectionHelperProps = {
  level: Level;
};

const SubsectionHelper = ({level, children}: PropsWithChildren<SubsectionHelperProps>) => (
  <SubsectionHelperContainer level={level}>
    <SubsectionHelperIcon level={level} />
    {children}
  </SubsectionHelperContainer>
);

export {HELPER_LEVEL_INFO, HELPER_LEVEL_WARNING, SubsectionHelper};
