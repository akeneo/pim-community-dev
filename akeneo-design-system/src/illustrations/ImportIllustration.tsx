import React from 'react';
import styled, {css, keyframes} from 'styled-components';
import {IllustrationProps} from './IllustrationProps';
import Import from '../../static/illustrations/Import.svg';

const arrowInAnimation = keyframes`
  0%   {transform: rotate(0deg)}
  100% {transform: rotate(180deg)}
`;

const arrowOutAnimation = keyframes`
  0%   {transform: rotate(180deg)}
  100% {transform: rotate(0deg)}
`;

const starsInAnimation = keyframes`
  0%   {transform: scale(1)}
  100% {transform: scale(1.2)}
`;

const starsOutAnimation = keyframes`
  0%   {transform: scale(1.2)}
  100% {transform: scale(1)}
`;

const Stars = styled.g`
  fill: #5e63b6;
  transform-origin: 50% 50%;
  animation-duration: 0.2s;
  animation-timing-function: linear;
  animation-fill-mode: forwards;
  animation-name: ${starsInAnimation};
`;

const Arrow = styled.g`
  fill: #9452ba;
  transform-origin: 51% 32%;
  animation-duration: 0.3s;
  animation-timing-function: ease-in-out;
  animation-fill-mode: forwards;
  animation-name: ${arrowInAnimation};
`;

const Container = styled.svg<{animateOnHover: boolean}>(
  ({animateOnHover}) =>
    animateOnHover &&
    css`
      &:not(:hover) ${Stars} {
        animation-name: ${starsOutAnimation};
      }

      &:not(:hover) ${Arrow} {
        animation-name: ${arrowOutAnimation};
      }
    `
);

const ImportIllustration = ({title, size = 256, animateOnHover = true, ...props}: IllustrationProps) => (
  <Container width={size} height={size} viewBox="0 0 256 256" animateOnHover={animateOnHover} {...props}>
    {title && <title>{title}</title>}
    <image href={Import} />
    <Stars className="stars">
      <path d="M218.1797,101.9522 C217.6267,101.9522 217.1797,101.5052 217.1797,100.9522 L217.1797,93.9522 C217.1797,93.3992 217.6267,92.9522 218.1797,92.9522 C218.7327,92.9522 219.1797,93.3992 219.1797,93.9522 L219.1797,100.9522 C219.1797,101.5052 218.7327,101.9522 218.1797,101.9522" />
      <path d="M221.6797,98.4522 L214.6797,98.4522 C214.1267,98.4522 213.6797,98.0052 213.6797,97.4522 C213.6797,96.8992 214.1267,96.4522 214.6797,96.4522 L221.6797,96.4522 C222.2327,96.4522 222.6797,96.8992 222.6797,97.4522 C222.6797,98.0052 222.2327,98.4522 221.6797,98.4522" />
      <path d="M212.251,159.4356 L208.313,159.4356 C208.037,159.4356 207.814,159.2116 207.814,158.9356 C207.814,158.6596 208.037,158.4356 208.313,158.4356 L212.251,158.4356 C212.527,158.4356 212.751,158.6596 212.751,158.9356 C212.751,159.2116 212.527,159.4356 212.251,159.4356" />
      <path d="M210.2822,161.4043 C210.0062,161.4043 209.7822,161.1803 209.7822,160.9043 L209.7822,156.9673 C209.7822,156.6903 210.0062,156.4673 210.2822,156.4673 C210.5582,156.4673 210.7822,156.6903 210.7822,156.9673 L210.7822,160.9043 C210.7822,161.1803 210.5582,161.4043 210.2822,161.4043" />
      <path d="M56.6792,48.4522 L49.6792,48.4522 C49.1272,48.4522 48.6792,48.0052 48.6792,47.4522 C48.6792,46.8992 49.1272,46.4522 49.6792,46.4522 L56.6792,46.4522 C57.2312,46.4522 57.6792,46.8992 57.6792,47.4522 C57.6792,48.0052 57.2312,48.4522 56.6792,48.4522" />
      <path d="M53.1792,51.9522 C52.6272,51.9522 52.1792,51.5052 52.1792,50.9522 L52.1792,43.9522 C52.1792,43.3992 52.6272,42.9522 53.1792,42.9522 C53.7312,42.9522 54.1792,43.3992 54.1792,43.9522 L54.1792,50.9522 C54.1792,51.5052 53.7312,51.9522 53.1792,51.9522" />
      <path d="M36.2822,117.4043 C36.0062,117.4043 35.7822,117.1803 35.7822,116.9043 L35.7822,112.9673 C35.7822,112.6903 36.0062,112.4673 36.2822,112.4673 C36.5582,112.4673 36.7822,112.6903 36.7822,112.9673 L36.7822,116.9043 C36.7822,117.1803 36.5582,117.4043 36.2822,117.4043" />
      <path d="M38.251,115.4356 L34.313,115.4356 C34.037,115.4356 33.814,115.2116 33.814,114.9356 C33.814,114.6596 34.037,114.4356 34.313,114.4356 L38.251,114.4356 C38.527,114.4356 38.751,114.6596 38.751,114.9356 C38.751,115.2116 38.527,115.4356 38.251,115.4356" />
    </Stars>
    <Arrow className="arrow">
      <path d="M130.4976,90.1905 C129.6686,90.1905 128.9976,89.5185 128.9976,88.6905 L128.9976,74.9785 C128.9976,74.1505 129.6686,73.4785 130.4976,73.4785 C131.3266,73.4785 131.9976,74.1505 131.9976,74.9785 L131.9976,88.6905 C131.9976,89.5185 131.3266,90.1905 130.4976,90.1905" />
      <path d="M130.4976,90.1905 C130.1136,90.1905 129.7296,90.0445 129.4366,89.7515 L124.5886,84.9035 C124.0026,84.3175 124.0026,83.3685 124.5886,82.7825 C125.1736,82.1965 126.1236,82.1965 126.7096,82.7825 L130.4976,86.5695 L134.2856,82.7825 C134.8716,82.1965 135.8206,82.1965 136.4066,82.7825 C136.9926,83.3685 136.9926,84.3175 136.4066,84.9035 L131.5586,89.7515 C131.2656,90.0445 130.8816,90.1905 130.4976,90.1905" />
    </Arrow>
  </Container>
);

ImportIllustration.StarsAnimation = arrowInAnimation;
ImportIllustration.ArrowAnimation = arrowInAnimation;

export {ImportIllustration};
