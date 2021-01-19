import React from 'react';
import styled, {css, keyframes} from 'styled-components';
import {IconProps} from './IconProps';

const anim = keyframes`
  from {transform: rotate(0) translate(0, 0)}
  to {transform: rotate(15deg) translate(-3px, -2px)}
`;

const Lid = styled.path`
  transform-origin: 60% 90%;
  animation: ${anim} 0.5s linear;
  animation-play-state: paused;
`;

const Container = styled.svg<{animateOnHover: boolean}>(
  ({animateOnHover}) =>
    animateOnHover &&
    css`
      :hover ${Lid} {
        animation-play-state: running;
      }
    `
);

const DeleteIcon = ({title, size = 24, color = 'currentColor', animateOnHover = true, ...props}: IconProps) => (
  <Container viewBox="0 0 24 24" width={size} height={size} animateOnHover={animateOnHover} {...props}>
    {title && <title>{title}</title>}
    <g stroke={color} fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 8h14v14H5zM8.5 11v7.5M12 11v7.5M15.5 11v7.5" />
      <Lid className="lid" d="M3 5h18v3H3zM8.5 2.5h7" />
    </g>
  </Container>
);

export {DeleteIcon};
