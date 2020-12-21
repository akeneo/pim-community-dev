import React, {ReactElement, ReactNode, Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';
import {DangerIcon, IconProps, InfoRoundIcon} from '../../icons';
import {useSkeleton} from '../../hooks';
import {applySkeletonStyle, SkeletonProps} from '../Skeleton/Skeleton';

const getBackgroundColor = (level: Level) => {
  switch (level) {
    case 'info':
      return getColor('blue', 10);
    case 'warning':
      return getColor('yellow', 10);
    case 'error':
      return getColor('red', 10);
  }
};

const getFontColor = (level: Level, inline: boolean) => {
  switch (level) {
    case 'info':
      return getColor('grey', 120);
    case 'warning':
      return getColor(inline ? 'grey' : 'yellow', 120);
    case 'error':
      return getColor('red', inline ? 100 : 120);
  }
};

const getIconColor = (level: Level, inline: boolean) => {
  switch (level) {
    case 'info':
      return getColor('blue', 100);
    case 'warning':
      return getColor('yellow', inline ? 100 : 120);
    case 'error':
      return getColor('red', inline ? 100 : 120);
  }
};

const getIcon = (level: Level): JSX.Element => {
  switch (level) {
    case 'info':
      return <InfoRoundIcon />;
    case 'warning':
      return <DangerIcon />;
    case 'error':
      return <DangerIcon />;
  }
};

const getSeparatorColor = (level: Level) => {
  switch (level) {
    case 'info':
      return getColor('grey', 80);
    case 'warning':
      return getColor('yellow', 120);
    case 'error':
      return getColor('red', 120);
  }
};

const getLinkColor = (level: Level, inline: boolean) => {
  switch (level) {
    case 'info':
      return getColor('blue', 100);
    case 'warning':
      return getColor('yellow', 120);
    case 'error':
      return getColor('red', inline ? 100 : 120);
  }
};

const Container = styled.div<{level: Level; inline: boolean} & SkeletonProps & AkeneoThemedProps>`
  display: flex;
  font-weight: 400;
  padding-right: 20px;
  color: ${props => getFontColor(props.level, props.inline)};

  ${props =>
    !props.inline &&
    css`
      min-height: 44px;
      background-color: ${getBackgroundColor(props.level)};
    `}

  ${applySkeletonStyle()}
`;

type Level = 'info' | 'warning' | 'error';

const IconContainer = styled.span<{level: Level; inline: boolean} & AkeneoThemedProps>`
  height: ${({inline}) => (inline ? '16px' : '20px')};
  margin: ${({inline}) => (inline ? '2px 0' : '12px 10px')};
  color: ${props => getIconColor(props.level, props.inline)};
`;

const TextContainer = styled.div<{level: Level; inline: boolean} & SkeletonProps & AkeneoThemedProps>`
  padding-left: ${({inline}) => (inline ? '4px' : '10px')};
  white-space: break-spaces;

  a {
    ${({inline, level, skeleton}) =>
      !skeleton &&
      css`
        color: ${getLinkColor(level, inline)};
      `};
    margin: 0 3px;
  }

  ${({inline, level, skeleton}) =>
    !inline &&
    !skeleton &&
    css`
      margin: 12px 0;
      border-left: 1px solid ${getSeparatorColor(level)};
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
  level?: Level;

  /**
   * Icon to display. If not provided, the Helper will display the corresponding level Icon.
   */
  icon?: ReactElement<IconProps>;

  /**
   * The content of the component.
   */
  children: ReactNode;
};

/** Helper informs the user about the features of the section. */
const Helper = React.forwardRef<HTMLDivElement, HelperProps>(
  ({level = 'info', inline = false, icon, children, ...rest}: HelperProps, forwardedRef: Ref<HTMLDivElement>) => {
    const skeleton = useSkeleton();

    return (
      <Container ref={forwardedRef} level={level} inline={inline} skeleton={skeleton} {...rest}>
        <IconContainer inline={inline} level={level}>
          {!skeleton && React.cloneElement(undefined === icon ? getIcon(level) : icon, {size: inline ? 16 : 20})}
        </IconContainer>
        <TextContainer level={level} inline={inline} skeleton={skeleton}>
          {children}
        </TextContainer>
      </Container>
    );
  }
);

export {Helper};
export type {HelperProps};
