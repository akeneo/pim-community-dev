import React, {ReactNode, Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';
import {DangerIcon, InfoRoundIcon} from '../../icons';

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

const getFontColor = (level: Level, inline: boolean) => {
  switch (level) {
    case 'info':
      return getColor('grey120');
    case 'warning':
      return inline ? getColor('yellow100') : getColor('yellow120');
    case 'error':
      return inline ? getColor('red100') : getColor('red120');
  }
};

const getIconColor = (level: Level, inline: boolean) => {
  switch (level) {
    case 'info':
      return getColor('blue100');
    case 'warning':
      return inline ? getColor('yellow100') : getColor('yellow120');
    case 'error':
      return inline ? getColor('red100') : getColor('red120');
  }
};

const getIcon = (level: Level, inline: boolean): JSX.Element => {
  const iconSize = inline ? 16 : 20;

  switch (level) {
    case 'info':
      return <InfoRoundIcon size={iconSize} />;
    case 'warning':
      return <DangerIcon size={iconSize} />;
    case 'error':
      return <DangerIcon size={iconSize} />;
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

const Container = styled.div<{level: Level; inline: boolean} & AkeneoThemedProps>`
  align-items: center;
  display: flex;
  font-weight: 400;
  padding-right: 15px;
  color: ${props => getFontColor(props.level, props.inline)};

  ${props =>
    !props.inline &&
    css`
      min-height: 24px;
      background-color: ${getBackgroundColor(props.level)};
    `}
`;

type Level = 'info' | 'warning' | 'error';

const IconContainer = styled.span<{level: Level; inline: boolean} & AkeneoThemedProps>`
  height: ${props => (props.inline ? '16px' : '20px')};
  padding-right: ${props => (props.inline ? '4px' : '12px')};
  color: ${props => getIconColor(props.level, props.inline)};

  ${props =>
    !props.inline &&
    css`
      margin: 12px 15px 12px 12px;
      border-right: 1px solid ${getSeparatorColor(props.level)};
    `}
`;

type HelperProps = {
  /**
   * Define the style of helper. Inline helper are located just below a field, normal helper are located below
   * the section title.
   */
  inline?: boolean;

  /**
   * Level of the helper defining its color and icon.
   */
  level: Level;

  /**
   * The content of the component.
   */
  children: ReactNode;
};

/** Helper informs the user about the features of the section */
const Helper = React.forwardRef<HTMLDivElement, HelperProps>(
  ({level, inline = false, children, ...rest}: HelperProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <Container ref={forwardedRef} level={level} inline={inline} {...rest}>
        <IconContainer inline={inline} level={level}>
          {getIcon(level, inline)}
        </IconContainer>
        {children}
      </Container>
    );
  }
);

export {Helper};
