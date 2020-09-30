import React, {Children, isValidElement, ReactNode, Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';
import {InfoIcon, DangerIcon} from '../../icons';
import {useContext} from 'react';
import {ThemeContext} from 'styled-components';

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

const getIcon = (level: Level, type: HelperType): JSX.Element => {
  const theme = useContext(ThemeContext);
  const size = type === 'inline' ? 16 : 20;

  switch (level) {
    case 'info':
      return <InfoIcon width={size} height={size} color={theme.color.blue100} />;
    case 'warning':
      return <DangerIcon width={size} height={size} color={theme.color.yellow120} />;
    case 'error':
      return <DangerIcon width={size} height={size} color={theme.color.red120} />;
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

const Container = styled.div<{type: HelperType; level: Level} & AkeneoThemedProps>`
  align-items: center;
  display: flex;
  font-weight: 600;
  padding-top: 10px;
  padding-bottom: 10px;
  padding-right: 15px;
  color: ${props => getFontColor(props.level)};

  ${props =>
    props.type !== 'inline' &&
    css`
      min-height: ${props.type === 'big' ? '100px' : '24px'};
    `};

  ${props =>
    props.type !== 'inline' &&
    css`
      background-color: ${getBackgroundColor(props.level)};
    `}
`;

const IconContainer = styled.span<{level: Level; type: HelperType} & AkeneoThemedProps>`
  ${props =>
    props.type !== 'inline' &&
    css`
      padding: ${props => (props.type === 'big' ? '20px' : '12px')};
      margin-right: 15px;
      border-right: 1px solid ${getSeparatorColor(props.level)};
    `};
`;

type Level = 'info' | 'warning' | 'error';
type HelperType = 'big' | 'small' | 'inline';

type HelperProps = {
  /**
   * Level of the helper defining it's color and icon.
   */
  level: Level;

  /**
   * Override the icon displayed, unless provided, the icon is mapped to the value of the level prop.
   */
  icon?: ReactNode;

  /**
   * Define the type of a helper.
   */
  type: HelperType;

  /**
   * The content of the component.
   */
  children: ReactNode;
};

const Helper = React.forwardRef<HTMLDivElement, HelperProps>(
  ({type, level, children, icon, ...rest}: HelperProps, forwardedRef: Ref<HTMLDivElement>) => {
    const titleChildren = Children.toArray(children).filter(
      child => isValidElement(child) && child.type === HelperTitle
    );
    const descriptionChildren = Children.toArray(children).filter(
      child => !isValidElement(child) || child.type !== HelperTitle
    );

    const resizedIcon = isValidElement(icon) && React.cloneElement(icon, {height: 120, width: 120});

    return (
      <Container ref={forwardedRef} type={type} level={level} {...rest}>
        <IconContainer level={level} type={type}>
          {resizedIcon || getIcon(level, type)}
        </IconContainer>
        <div>
          {titleChildren}
          {descriptionChildren}
        </div>
      </Container>
    );
  }
);

const HelperTitle = styled.div`
  color: ${props => props.theme.color.grey140};
  font-size: ${props => props.theme.fontSize.bigger};
  font-weight: 600;
`;

export {Helper, HelperTitle};
