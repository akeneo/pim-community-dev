import React from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, Level} from '../../theme';

const BadgeContainer = styled.span<BadgeProps & AkeneoThemedProps>`
  display: inline-block;
  height: 18px;
  line-height: 16px;
  border: 1px solid;
  padding: 0 6px;
  border-radius: 2px;
  text-transform: uppercase;
  box-sizing: border-box;
  background-color: ${({theme}: AkeneoThemedProps) => theme.color.white};
  font-size: ${({theme}: AkeneoThemedProps) => theme.fontSize.small};

  ${({level = 'primary', theme}: BadgeProps & AkeneoThemedProps) => css`
    color: ${theme.color[`${theme.palette[level]}140`]};
    border-color: ${theme.color[`${theme.palette[level]}100`]};
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

const Badge = ({level = 'primary', children}: BadgeProps) => {
  return <BadgeContainer level={level}>{children}</BadgeContainer>;
};

export {Badge};
