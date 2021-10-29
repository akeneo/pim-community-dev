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
  z-index: 1901;
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
     * Horizontal position of the overlay (forced)
     */
    horizontalPosition?: HorizontalPosition;

    /**
     * When dropdown is open, it will keep the opener element displayed.
     */
    dropdownOpenerVisible?: boolean;

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
  z-index: 1900;
`;

const getOverlayPosition = (
  verticalPosition?: VerticalPosition,
  horizontalPosition?: HorizontalPosition,
  dropdownOpenerVisible?: boolean,
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

  let top =
    'up' === verticalPosition
      ? parentRect.bottom - elementRect.height + BORDER_SHADOW_OFFSET
      : parentRect.top - BORDER_SHADOW_OFFSET;

  if (dropdownOpenerVisible) {
    top = 'up' === verticalPosition ? parentRect.top - elementRect.height : parentRect.bottom + 1;
  }

  const left = 'left' === horizontalPosition ? parentRect.right - elementRect.width : parentRect.left;

  return [top, left];
};

const Overlay = ({
  verticalPosition,
  horizontalPosition,
  dropdownOpenerVisible = false,
  parentRef,
  onClose,
  children,
  ...rest
}: OverlayProps) => {
  const [overlayPosition, setOverlayPosition] = useState<number[]>([0, 0]);
  const portalNode = document.createElement('div');
  portalNode.setAttribute('id', 'dropdown-root');
  const portalRef = useRef<HTMLDivElement>(portalNode);
  const overlayRef = useRef<HTMLDivElement>(null);

  const overlayVerticalPosition = useVerticalPosition(overlayRef, verticalPosition);
  const overlayHorizontalPosition = useHorizontalPosition(overlayRef, horizontalPosition);
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

  useEffect(() => {
    setOverlayPosition(
      getOverlayPosition(
        overlayVerticalPosition,
        overlayHorizontalPosition,
        dropdownOpenerVisible,
        parentRef,
        overlayRef
      )
    );
  }, [children, overlayVerticalPosition, overlayHorizontalPosition, parentRef, overlayRef, dropdownOpenerVisible]);

  const [top, left] = overlayPosition;

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
