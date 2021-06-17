import React, {ReactNode, useRef, useState, useEffect, RefObject} from 'react';
import styled, {css} from 'styled-components';
import {Key, Override} from '../../../shared';
import {
  HorizontalPosition,
  useHorizontalPosition,
  useShortcut,
  useVerticalPosition,
  VerticalPosition,
} from '../../../hooks';
import {AkeneoThemedProps, CommonStyle, getColor} from '../../../theme';
import {createPortal} from "react-dom";

const Container = styled.div<
  {
    visible: boolean;
    verticalPosition: VerticalPosition;
    horizontalPosition: HorizontalPosition;
    parentRect: DOMRect
  } & AkeneoThemedProps
>`
  ${CommonStyle}
  background: ${getColor('white')};
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  padding: 10px 0;
  max-width: 400px;
  min-width: 150px;
  position: absolute;
  opacity: ${({visible}) => (visible ? 1 : 0)};
  transition: opacity 0.15s ease-in-out;
  z-index: 11;

  ${({verticalPosition, parentRect}) =>
    'up' === verticalPosition
      ? css`
          bottom: ${parentRect.top + parentRect.height}px;
        `
      : css`
          top: ${parentRect.top}px;
        `}
  ${({horizontalPosition, parentRect}) =>
    'left' === horizontalPosition
      ? css`
          right: ${parentRect.left + parentRect.width}px;
        `
      : css`
          left: ${parentRect.left}px;
        `};
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
  z-index: 10;
`;

const Overlay = ({verticalPosition, parentRef, onClose, children, ...rest}: OverlayProps) => {
  const portalNode = document.createElement('div');
  portalNode.setAttribute('id', 'dropdown-root');
  const overlayRef = useRef<HTMLDivElement>(portalNode);

  verticalPosition = useVerticalPosition(overlayRef, verticalPosition);
  const horizontalPosition = useHorizontalPosition(overlayRef);
  const [visible, setVisible] = useState<boolean>(false);
  useShortcut(Key.Escape, onClose);

  useEffect(() => {
    setVisible(true);
    document.body.appendChild(overlayRef.current);

    return () => {
      document.body.removeChild(overlayRef.current);
    };
  }, []);

  if (undefined === parentRef || null === parentRef.current) {
    return null;
  }

  return createPortal(
    <>
      <Backdrop data-testid="backdrop" onClick={onClose} />
      <Container
        visible={visible}
        parentRect={parentRef.current.getBoundingClientRect()}
        horizontalPosition={horizontalPosition}
        verticalPosition={verticalPosition}
        {...rest}
      >
        {children}
      </Container>
    </>,
    overlayRef.current
  );
};

Overlay.displayName = 'Overlay';

export {Overlay};
