import React, {FC, useCallback, useLayoutEffect} from 'react';

import {HighlightElement, isIntersectingHighlight} from '../../../../helper';
import {useHighlightsContext} from '../../../../context/HighlightsContext';
import {useHighlightableContentContext} from '../../../../context/HighlightableContentContext';

export type HighlightPopoverDisclosureProps = {
  element: HTMLElement;
  show: () => void;
  hide: () => void;
  setActiveHighlight: (highlight: HighlightElement | null) => void;
  setActiveElement: (element: HTMLElement | null) => void;
};

const HighlightPopoverDisclosure: FC<HighlightPopoverDisclosureProps> = ({
  element,
  show,
  hide,
  setActiveHighlight,
  setActiveElement,
}) => {
  const {isActive} = useHighlightableContentContext();
  const {highlights} = useHighlightsContext();

  const findActiveHighlight = useCallback(
    (x: number, y: number) => {
      const highlight = highlights.find(highlight => {
        return isIntersectingHighlight(x, y, highlight);
      });

      return highlight || null;
    },
    [highlights]
  );

  useLayoutEffect(() => {
    let requestAnimationFrameId: number | null = null;

    const handleMouseMove = (event: MouseEvent) => {
      if (highlights.length === 0) {
        return;
      }

      const eventClientX = event.clientX;
      const eventClientY = event.clientY;

      requestAnimationFrameId = window.requestAnimationFrame(() => {
        const activeHighlight = findActiveHighlight(eventClientX, eventClientY);

        if (activeHighlight) {
          setActiveHighlight(activeHighlight);
          setActiveElement(element);
          show();
        } else {
          hide();
          setActiveElement(null);
          setActiveHighlight(null);
        }
      });
    };

    if (isActive && element) {
      element.addEventListener('mousemove', handleMouseMove as EventListener);
    }

    return () => {
      if (isActive && element) {
        element.removeEventListener('mousemove', handleMouseMove as EventListener);
      }
      if (requestAnimationFrameId !== null) {
        window.cancelAnimationFrame(requestAnimationFrameId);
      }
    };
  }, [isActive, element, findActiveHighlight, highlights]);

  return <></>;
};

export default HighlightPopoverDisclosure;
