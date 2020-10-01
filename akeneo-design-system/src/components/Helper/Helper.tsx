import React, {ReactNode, Ref} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';
import {InfoIcon, DangerIcon} from '../../icons';

const getBackgroundColor = (level: Level) => {
  switch (level) {
    case 'info':
      return getColor('blue10');
    case 'warning':
      return getColor('yellow10');
    case 'error':
      return getColor('red10');
  }
};

const getFontColor = (level: Level) => {
  switch (level) {
    case 'info':
      return getColor('grey120');
    case 'warning':
      return getColor('yellow120');
    case 'error':
      return getColor('red120');
  }
};

const getIconColor = (level: Level) => {
  switch (level) {
    case 'info':
      return getColor('blue100');
    case 'warning':
      return getColor('yellow120');
    case 'error':
      return getColor('red120');
  }
};

const getIcon = (level: Level): JSX.Element => {
  switch (level) {
    case 'info':
      return <InfoIcon size={20} />;
    case 'warning':
      return <DangerIcon size={20} />;
    case 'error':
      return <DangerIcon size={20} />;
  }
};

const getSeparatorColor = (level: Level) => {
  switch (level) {
    case 'info':
      return getColor('grey80');
    case 'warning':
      return getColor('yellow120');
    case 'error':
      return getColor('red120');
  }
};

const Container = styled.div<{level: Level} & AkeneoThemedProps>`
  align-items: center;
  display: flex;
  font-weight: 600;
  padding-right: 15px;
  color: ${props => getFontColor(props.level)};
  min-height: 24px;
  background-color: ${props => getBackgroundColor(props.level)};
`;

const IconContainer = styled.span<{level: Level} & AkeneoThemedProps>`
  height: 20px;
  padding-right: 12px;
  margin: 12px 15px 12px 12px;
  border-right: 1px solid ${props => getSeparatorColor(props.level)};
  color: ${props => getIconColor(props.level)};
`;

type Level = 'info' | 'warning' | 'error';

type HelperProps = {
  /**
   * Level of the helper defining it's color and icon.
   */
  level: Level;

  /**
   * The content of the component.
   */
  children: ReactNode;
};

const Helper = React.forwardRef<HTMLDivElement, HelperProps>(
  ({level, children, ...rest}: HelperProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <Container ref={forwardedRef} level={level} {...rest}>
        <IconContainer level={level}>{getIcon(level)}</IconContainer>
        <div>{children}</div>
      </Container>
    );
  }
);

export {Helper};
