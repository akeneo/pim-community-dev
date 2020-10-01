import React, {ReactNode, Ref} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';
import {InfoIcon, DangerIcon} from '../../icons';

const getFontColor = (level: Level) => {
  switch (level) {
    case 'info':
      return getColor('grey120');
    case 'warning':
      return getColor('grey120');
    case 'error':
      return getColor('red100');
  }
};

const getIconColor = (level: Level) => {
  switch (level) {
    case 'info':
      return getColor('blue100');
    case 'warning':
      return getColor('yellow100');
    case 'error':
      return getColor('red100');
  }
};

const getIcon = (level: Level): JSX.Element => {
  switch (level) {
    case 'info':
      return <InfoIcon width={16} height={16} />;
    case 'warning':
      return <DangerIcon width={16} height={16} />;
    case 'error':
      return <DangerIcon width={16} height={16} />;
  }
};

const Container = styled.div<{level: Level} & AkeneoThemedProps>`
  align-items: center;
  display: flex;
  font-weight: 600;
  padding-right: 15px;
  color: ${props => getFontColor(props.level)};
`;

const IconContainer = styled.div<{level: Level} & AkeneoThemedProps>`
  height: 16px;
  margin-right: 4px;
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

const InlineHelper = React.forwardRef<HTMLDivElement, HelperProps>(
  ({level, children, ...rest}: HelperProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <Container ref={forwardedRef} level={level} {...rest}>
        <IconContainer level={level}>{getIcon(level)}</IconContainer>
        <div>{children}</div>
      </Container>
    );
  }
);

export {InlineHelper};
