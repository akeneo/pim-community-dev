import React from 'react';
import styled, {css, keyframes} from 'styled-components';
import {IconProps} from './IconProps';

const downloadAnimation = keyframes`
  0%   {transform: translateY(0)}
  25%  {transform: translateY(2px)}
  50%  {transform: translateY(-2px)}
  100% {transform: translateY(0)}
`;

const Arrow = styled.path`
  animation-duration: 0.5s;
  animation-iteration-count: 1;
`;

const animatedMixin = css`
  ${Arrow} {
    animation-name: ${downloadAnimation};
  }
`;

const Container = styled.svg<{animateOnHover: boolean}>`
  :hover {
    ${({animateOnHover}) => animateOnHover && animatedMixin}
  }
`;

const DownloadIcon = ({title, size = 24, color = 'currentColor', animateOnHover = true, ...props}: IconProps) => (
  <Container viewBox="0 0 24 24" width={size} height={size} animateOnHover={animateOnHover} {...props}>
    {title && <title>{title}</title>}
    <g stroke={color} fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path d="M17.11 17H20v5H4v-5h3" />
      <Arrow d="M12 2v16V2zM17 13l-5 5.5L7 13h0" />
    </g>
  </Container>
);

DownloadIcon.animatedMixin = animatedMixin;

export {DownloadIcon};
