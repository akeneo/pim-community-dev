import React, {ReactNode, useRef, useState, useEffect} from 'react';
import styled, {css} from 'styled-components';
import {Key} from '../../../shared';
import {useShortcut} from '../../../hooks';
import {AkeneoThemedProps, getColor} from '../../../theme';

type VerticalPosition = 'up' | 'down';
type HorizontalPosition = 'left' | 'right';

const Container = styled.div<
  {
    visible: boolean;
    verticalPosition: VerticalPosition;
    horizontalPosition: HorizontalPosition;
  } & AkeneoThemedProps
>`
  background: ${getColor('white')};
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  padding: 0 0 10px 0;
  max-width: 320px;
  min-width: 150px;
  position: absolute;
  opacity: ${({visible}) => (visible ? 1 : 0)};
  transition: opacity 0.15s ease-in-out;
  z-index: 2;

  ${({verticalPosition}) =>
    'up' === verticalPosition
      ? css`
          bottom: -1px;
        `
      : css`
          top: -1px;
        `}
  ${({horizontalPosition}) =>
    'left' === horizontalPosition
      ? css`
          right: -1px;
        `
      : css`
          left: -1px;
        `};
`;

type OverlayProps = {
  /**
   * Vertical position of the overlay (forced)
   */
  verticalPosition?: VerticalPosition;

  /**
   * What to do on overlay closing
   */
  onClose: () => void;

  children: ReactNode;
};

const Backdrop = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
`;

const Overlay = ({verticalPosition: defaultVerticalPosition, onClose, children}: OverlayProps) => {
  const overlayRef = useRef<HTMLDivElement>(null);
  const [verticalPosition, setVerticalPosition] = useState<VerticalPosition>(defaultVerticalPosition ?? 'down');
  const [horizontalPosition, setHorizontalPosition] = useState<HorizontalPosition>('right');
  const [visible, setVisible] = useState<boolean>(false);
  useShortcut(Key.Escape, onClose);

  useEffect(() => {
    if (null !== overlayRef.current) {
      if (undefined === defaultVerticalPosition) {
        const elementHeight = overlayRef.current.getBoundingClientRect().height;
        const windowHeight = window.innerHeight;
        const distanceToTop = overlayRef.current.getBoundingClientRect().top;
        const distanceToBottom = windowHeight - (elementHeight + distanceToTop);

        setVerticalPosition(distanceToTop > distanceToBottom ? 'up' : 'down');
      }

      if (null !== overlayRef.current) {
        const elementWidth = overlayRef.current.getBoundingClientRect().width;
        const windowWidth = window.innerWidth;
        const distanceToLeft = overlayRef.current.getBoundingClientRect().left;
        const distanceToRight = windowWidth - (elementWidth + distanceToLeft);

        setHorizontalPosition(distanceToLeft > distanceToRight ? 'left' : 'right');
      }
      setVisible(true);
    }
  }, [defaultVerticalPosition]);

  return (
    <>
      <Backdrop data-testid="backdrop" onClick={onClose} />
      <Container
        ref={overlayRef}
        visible={visible}
        horizontalPosition={horizontalPosition}
        verticalPosition={verticalPosition}
      >
        {children}
      </Container>
    </>
  );
};

export {Overlay};
