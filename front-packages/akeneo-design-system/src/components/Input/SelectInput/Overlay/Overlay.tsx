import React, {ReactNode, RefObject, useEffect, useRef} from 'react';
import styled from 'styled-components';
import {VerticalPosition, useVerticalPosition, useWindowResize} from '../../../../hooks';
import {AkeneoThemedProps, CommonStyle, getColor} from '../../../../theme';
import {createPortal} from 'react-dom';

const OverlayContent = styled.div<{top: number; width: number; left: number} & AkeneoThemedProps>`
  ${CommonStyle}
  background: ${getColor('white')};
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  padding: 10px 0 10px 0;
  position: fixed;
  transition: opacity 0.15s ease-in-out;
  z-index: 2001;
  top: ${({top}) => top}px;
  left: ${({left}) => left}px;
  width: ${({width}) => width}px;
`;

const OverlayContainer = styled.div``;

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
  /** @private */
  parentRef?: RefObject<HTMLDivElement>;
};

const getOverlayPosition = (
  verticalPosition?: VerticalPosition,
  parentRef?: RefObject<HTMLDivElement>,
  elementRef?: RefObject<HTMLDivElement>
) => {
  if (
    undefined === parentRef ||
    undefined === elementRef ||
    null === parentRef.current ||
    null === elementRef.current
  ) {
    return [0, 0, 0];
  }

  const parentRect = parentRef.current.getBoundingClientRect();
  const elementRect = elementRef.current.getBoundingClientRect();

  const top = 'up' === verticalPosition ? parentRect.top - elementRect.height : parentRect.bottom;

  const left = parentRect.left;
  const width = parentRect.width;

  return [top, left, width];
};

const Overlay = ({verticalPosition, parentRef, onClose, children}: OverlayProps) => {
  const portalNode = document.createElement('div');
  portalNode.setAttribute('id', 'selectinput-root');
  const portalRef = useRef<HTMLDivElement>(portalNode);
  const overlayRef = useRef<HTMLDivElement>(null);
  verticalPosition = useVerticalPosition(overlayRef, verticalPosition);
  useWindowResize();

  useEffect(() => {
    document.body.appendChild(portalRef.current);

    return () => {
      document.body.removeChild(portalRef.current);
    };
  }, []);

  const [top, left, width] = getOverlayPosition(verticalPosition, parentRef, overlayRef);

  return createPortal(
    <OverlayContainer>
      <Backdrop data-testid="backdrop" onClick={onClose} />
      <OverlayContent top={top} left={left} width={width} ref={overlayRef} verticalPosition={verticalPosition}>
        {children}
      </OverlayContent>
    </OverlayContainer>,
    portalRef.current
  );
};

export {Overlay};
