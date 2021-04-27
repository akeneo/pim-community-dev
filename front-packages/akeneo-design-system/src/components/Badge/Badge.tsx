import React, {ReactNode, Ref} from 'react';
import {Override} from '../../shared';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getColorForLevel, getFontSize, Level} from '../../theme';

const BadgeContainer = styled.span<BadgeProps & AkeneoThemedProps>`
  display: inline-flex;
  height: 18px;
  line-height: 16px;
  border: 1px solid;
  padding: 0 6px;
  border-radius: 2px;
  text-transform: uppercase;
  box-sizing: border-box;
  background-color: ${getColor('white')};
  font-size: ${getFontSize('small')};
  font-weight: normal;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;

  ${({level = 'primary'}: BadgeProps & AkeneoThemedProps) => css`
    color: ${getColorForLevel(level, 140)};
    border-color: ${getColorForLevel(level, 100)};
  `}
`;

type BadgeProps = Override<
  React.HTMLAttributes<HTMLSpanElement>,
  {
    /**
     * Level of the Badge defining it's color and outline.
     */
    level?: Level;

    /**
     * Children of the Badge.
     */
    children?: ReactNode;
  }
>;

/**
 * Badges are used for items that must be: tagged, categorized, organized by keywords, or to highlight information.
 */
const Badge = React.forwardRef<HTMLSpanElement, BadgeProps>(
  ({level = 'primary', children, ...rest}: BadgeProps, forwardedRef: Ref<HTMLSpanElement>) => {
    return (
      <BadgeContainer level={level} ref={forwardedRef} {...rest}>
        {children}
      </BadgeContainer>
    );
  }
);

export {Badge};
export type {BadgeProps};
