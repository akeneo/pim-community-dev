import React from 'react';
import styled, {css} from 'styled-components';
import {IconProps} from './IconProps';

const Lid = styled.path`
  transition: transform 0.1s linear;
  transform-origin: 60% 90%;
`;

const animatedMixin = css`
  ${Lid} {
    transform: rotate(15deg) translate(-3px, -2px);
  }
`;

const Container = styled.svg<{animateOnHover: boolean}>`
  :hover {
    ${({animateOnHover}) => animateOnHover && animatedMixin}
  }
`;

const DeleteIcon = ({title, size = 24, color = 'currentColor', animateOnHover = true, ...props}: IconProps) => (
  <Container viewBox="0 0 24 24" width={size} height={size} animateOnHover={animateOnHover} {...props}>
    {title && <title>{title}</title>}
    <g stroke={color} fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 8h14v14H5zM8.5 11v7.5M12 11v7.5M15.5 11v7.5" />
      <Lid d="M3 5h18v3H3zM8.5 2.5h7" />
    </g>
  </Container>
);

DeleteIcon.animatedMixin = animatedMixin;

export {DeleteIcon};
