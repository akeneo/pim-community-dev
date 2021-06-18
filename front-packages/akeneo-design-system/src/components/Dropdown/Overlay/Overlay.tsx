import React, {ReactNode, useRef, useState, useEffect, RefObject} from 'react';
import {createPortal} from 'react-dom';
import styled from 'styled-components';
import {Key, Override} from '../../../shared';
import {
  HorizontalPosition,
  useHorizontalPosition,
  useShortcut,
  useVerticalPosition,
  useWindowResize,
  VerticalPosition,
} from '../../../hooks';
import {AkeneoThemedProps, CommonStyle, getColor} from '../../../theme';

const BORDER_SHADOW_OFFSET = 2;

const Container = styled.div<
  {
    visible: boolean;
    top: number;
    left: number;
  } & AkeneoThemedProps
>`
  ${CommonStyle}
  background: ${getColor('white')};
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  padding: 10px 0;
  max-width: 400px;
  min-width: 150px;
  position: fixed;
  opacity: ${({visible}) => (visible ? 1 : 0)};
  transition: opacity 0.15s ease-in-out;
  z-index: 1001;
  top: ${({top}) => top}px;
  left: ${({left}) => left}px;
`;

type OverlayProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
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

const Backdrop = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1000;
`;

const getOverlayPosition = (
  verticalPosition?: VerticalPosition,
  horizontalPosition?: HorizontalPosition,
  parentRef?: RefObject<HTMLDivElement>,
  elementRef?: RefObject<HTMLDivElement>
) => {
  if (
    undefined === parentRef ||
    undefined === elementRef ||
    null === parentRef.current ||
    null === elementRef.current
  ) {
    return [0, 0];
  }

  const parentRect = parentRef.current.getBoundingClientRect();
  const elementRect = elementRef.current.getBoundingClientRect();

  const top =
    'up' === verticalPosition
      ? parentRect.bottom - elementRect.height + BORDER_SHADOW_OFFSET
      : parentRect.top - BORDER_SHADOW_OFFSET;

  const left =
    'left' === horizontalPosition
      ? parentRect.right - elementRect.width + BORDER_SHADOW_OFFSET
      : parentRect.left - BORDER_SHADOW_OFFSET;

  return [top, left];
};

const Overlay = ({verticalPosition, parentRef, onClose, children, ...rest}: OverlayProps) => {
  const portalNode = document.createElement('div');
  portalNode.setAttribute('id', 'dropdown-root');
  const portalRef = useRef<HTMLDivElement>(portalNode);
  const overlayRef = useRef<HTMLDivElement>(null);

  verticalPosition = useVerticalPosition(overlayRef, verticalPosition);
  const horizontalPosition = useHorizontalPosition(overlayRef);
  const [visible, setVisible] = useState<boolean>(false);
  useShortcut(Key.Escape, onClose);
  useWindowResize();

  useEffect(() => {
    setVisible(true);
    document.body.appendChild(portalRef.current);

    return () => {
      document.body.removeChild(portalRef.current);
    };
  }, []);

  const [top, left] = getOverlayPosition(verticalPosition, horizontalPosition, parentRef, overlayRef);

  return createPortal(
    <>
      <Backdrop data-testid="backdrop" onClick={onClose} />
      <Container ref={overlayRef} visible={visible} top={top} left={left} {...rest}>
        {children}
      </Container>
    </>,
    portalRef.current
  );
};

Overlay.displayName = 'Overlay';

export {Overlay};
