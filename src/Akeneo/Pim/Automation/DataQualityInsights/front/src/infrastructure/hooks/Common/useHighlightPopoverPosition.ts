import {RefObject, useLayoutEffect, useState, useRef} from 'react';
import {HighlightElement} from '../../../application/helper';

const POPOVER_BOTTOM_PLACEMENT_OFFSET = 2;
const POPOVER_LEFT_PLACEMENT_OFFSET = 0;
const POPOVER_TOP_PLACEMENT_MARGE = 20;
const POPOVER_RIGHT_PLACEMENT_MARGE = 20;
const CONTAINER_SELECTOR = '.entity-edit-form.edit-form';

type Position = {
  top: number;
  left: number;
};

const useHighlightPopoverPosition = (
  highlight: HighlightElement | null,
  popoverRef: RefObject<HTMLDivElement>,
  isVisible: boolean
) => {
  const [position, setPosition] = useState<Position>({top: 0, left: 0});
  const container = useRef<HTMLElement>(document.querySelector(CONTAINER_SELECTOR));

  useLayoutEffect(() => {
    const element = highlight && highlight.domRange ? highlight.domRange : null;
    const popoverElement = popoverRef && popoverRef.current ? popoverRef.current : null;

    if (element && container.current && popoverElement) {
      // @fixme can cause slow performance issue ([Violation] 'requestAnimationFrame' handler took 103ms)
      const highlightRect = element.getBoundingClientRect();

      let topPosition = highlightRect.bottom + POPOVER_BOTTOM_PLACEMENT_OFFSET;
      if (
        highlightRect.bottom + popoverElement.clientHeight + POPOVER_TOP_PLACEMENT_MARGE >
        container.current.clientHeight
      ) {
        topPosition = highlightRect.top - POPOVER_BOTTOM_PLACEMENT_OFFSET - popoverElement.clientHeight;
      }

      let leftPosition = highlightRect.left + POPOVER_LEFT_PLACEMENT_OFFSET;
      if (
        highlightRect.left + popoverElement.clientWidth + POPOVER_RIGHT_PLACEMENT_MARGE >
        container.current.clientWidth
      ) {
        leftPosition = highlightRect.right - POPOVER_LEFT_PLACEMENT_OFFSET - popoverElement.clientWidth;
      }

      setPosition({
        top: topPosition,
        left: leftPosition,
      });
    }
  }, [highlight, popoverRef, isVisible]);

  return position;
};

export default useHighlightPopoverPosition;
