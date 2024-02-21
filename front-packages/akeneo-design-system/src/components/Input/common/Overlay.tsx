import React, {HTMLAttributes, ReactNode, RefObject, useEffect, useRef, useState} from 'react';
import {createPortal} from 'react-dom';
import styled from 'styled-components';
import {VerticalPosition, useVerticalPosition, useWindowResize} from '../../../hooks';
import {AkeneoThemedProps, CommonStyle, getColor} from '../../../theme';
import {Override} from '../../../shared';

const OverlayContent = styled.div<{visible: number; top: number; width: number; left: number} & AkeneoThemedProps>`
  ${CommonStyle}
  background: ${getColor('white')};
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  padding: 10px 0 10px 0;
  position: fixed;
  opacity: ${({visible}) => (visible ? 1 : 0)};
  transition: opacity 0.15s ease-in-out;
  z-index: 2001;
  top: ${({top}) => top}px;
  left: ${({left}) => left}px;
  width: ${({width}) => width}px;
`;

const Backdrop = styled.div`
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 2000;
`;

type OverlayProps = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Vertical position of the overlay (forced).
     */
    verticalPosition?: VerticalPosition;

    /**
     * What to do on overlay closing.
     */
    onClose: () => void;

    children: ReactNode;

    /** @private */
    parentRef?: RefObject<HTMLDivElement>;
  }
>;

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

const Overlay = ({verticalPosition, parentRef, onClose, children, ...rest}: OverlayProps) => {
  const portalNode = document.createElement('div');
  portalNode.setAttribute('id', 'input-overlay-root');
  const portalRef = useRef<HTMLDivElement>(portalNode);
  const overlayRef = useRef<HTMLDivElement>(null);
  const [visible, setVisible] = useState<boolean>(false);
  const [overlayPosition, setOverlayPosition] = useState<number[]>([0, 0, 0]);
  const overlayVerticalPosition = useVerticalPosition(overlayRef, verticalPosition);
  useWindowResize();

  useEffect(() => {
    setVisible(true);
    document.body.appendChild(portalRef.current);

    return () => {
      document.body.removeChild(portalRef.current);
    };
  }, []);

  React.useEffect(() => {
    setOverlayPosition(getOverlayPosition(overlayVerticalPosition, parentRef, overlayRef));
  }, [children, overlayVerticalPosition, parentRef, overlayRef]);

  const [top, left, width] = overlayPosition;

  return createPortal(
    <>
      <Backdrop data-testid="backdrop" onClick={onClose} />
      <OverlayContent ref={overlayRef} visible={visible} top={top} left={left} width={width} {...rest}>
        {children}
      </OverlayContent>
    </>,
    portalRef.current
  );
};

export {Overlay};
