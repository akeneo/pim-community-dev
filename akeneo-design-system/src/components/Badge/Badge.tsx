import React from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getColorForLevel, getFontSize, Level} from 'theme';

const BadgeContainer = styled.span<BadgeProps & AkeneoThemedProps>`
  display: inline-block;
  height: 18px;
  line-height: 16px;
  border: 1px solid;
  padding: 0 6px;
  border-radius: 2px;
  text-transform: uppercase;
  box-sizing: border-box;
  background-color: ${getColor('white')};
  font-size: ${getFontSize('small')};

  ${({level = 'primary'}: BadgeProps & AkeneoThemedProps) => css`
    color: ${getColorForLevel(level, 140)};
    border-color: ${getColorForLevel(level, 100)};
  `}
`;

type BadgeProps = {
  /**
   * Level of the Badge defining it's color and outline.
   */
  level?: Level;

  /**
   * Children of the Badge, can only be string for a Badge.
   */
  children?: string;
};

/**
 * Badges are used for items that must be: tagged, categorized, organized by keywords, or to highlight information.
 */
const Badge = ({level = 'primary', children}: BadgeProps) => {
  return <BadgeContainer level={level}>{children}</BadgeContainer>;
};

export {Badge};
