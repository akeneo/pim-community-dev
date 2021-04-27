import React, {ReactNode, useRef} from 'react';
import styled, {css} from 'styled-components';
import {VerticalPosition, useVerticalPosition} from '../../../../hooks';
import {AkeneoThemedProps, getColor} from '../../../../theme';

const OverlayContent = styled.div<{verticalPosition: VerticalPosition} & AkeneoThemedProps>`
  background: ${getColor('white')};
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  padding: 10px 0 10px 0;
  position: absolute;
  transition: opacity 0.15s ease-in-out;
  z-index: 2;
  left: 0;
  right: 0;

  ${({verticalPosition}) =>
    'up' === verticalPosition
      ? css`
          bottom: 46px;
        `
      : css`
          top: 6px;
        `};
`;

const OverlayContainer = styled.div`
  position: relative;
`;

const Backdrop = styled.div`
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
`;

type OverlayProps = {
  verticalPosition?: VerticalPosition;
  onClose: () => void;
  children: ReactNode;
};

const Overlay = ({verticalPosition, onClose, children}: OverlayProps) => {
  const overlayRef = useRef<HTMLDivElement>(null);
  verticalPosition = useVerticalPosition(overlayRef, verticalPosition);

  return (
    <OverlayContainer>
      <Backdrop data-testid="backdrop" onClick={onClose} />
      <OverlayContent ref={overlayRef} verticalPosition={verticalPosition}>
        {children}
      </OverlayContent>
    </OverlayContainer>
  );
};

export {Overlay};
