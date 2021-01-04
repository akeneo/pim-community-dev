import {useShortcut} from 'hooks';
import React, {ReactNode, useRef, useState, useEffect} from 'react';
import {Key} from '../../../shared/key';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from 'theme';

const Container = styled.div<
  {
    visible: boolean;
    verticalPosition: 'up' | 'down';
    horizontalPosition: 'left' | 'right';
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
  position?: 'up' | 'down';
  onClose: () => void;
  children: ReactNode;
};

const Overlay = ({position, onClose, children}: OverlayProps) => {
  const overlayRef = useRef<HTMLDivElement>(null);
  const [verticalPosition, setVerticalPosition] = useState<'up' | 'down'>(position ?? 'down');
  const [horizontalPosition, setHorizontalPosition] = useState<'left' | 'right'>('right');
  const [visible, setVisible] = useState<boolean>(false);
  useShortcut(Key.Escape, onClose);

  useEffect(() => {
    if (null !== overlayRef.current) {
      if (undefined === position) {
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
  }, []);

  return (
    <Container
      ref={overlayRef}
      visible={visible}
      horizontalPosition={horizontalPosition}
      verticalPosition={verticalPosition}
    >
      {children}
    </Container>
  );
};

Overlay.displayName = 'Dropdown.Overlay';
export {Overlay};
