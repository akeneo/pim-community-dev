import React, {useCallback, useLayoutEffect, useRef, useState} from "react";
import {useGetSpellcheckPopover} from "../../../../../infrastructure/hooks";
import PopoverContent from "./Spellchecker/PopoverContent";

const POPOVER_BOTTOM_PLACEMENT_OFFSET = 2;
const POPOVER_TOP_PLACEMENT_MARGE = 20;
const PEF_CONTAINER_SELECTOR = '.entity-edit-form.edit-form';

interface PopoverStyleState {
  left?: number;
  top?: number;
}

const SpellCheckerPopover = () => {
  const {isOpen, highlightRef, mistake, widgetId, handleOpening, handleClosing} = useGetSpellcheckPopover();
  const [style, setStyle] = useState<PopoverStyleState>({});
  const popoverRef = useRef<HTMLDivElement>(null);
  const classList = ["AknSpellCheck-popover"];

  const handleMouseEnter = useCallback(() => {
    handleOpening(widgetId,mistake, highlightRef);
  }, [widgetId, highlightRef, mistake, handleOpening]);

  const handleMouseLeave = useCallback(() => {
    handleClosing();
  }, [handleClosing]);

  useLayoutEffect(() => {
    const element = highlightRef && highlightRef.current ?  highlightRef.current : null;
    const container = document.querySelector(PEF_CONTAINER_SELECTOR);
    const popoverElement = popoverRef && popoverRef.current ? popoverRef.current : null;

    if (element && container && popoverElement) {
      const highlightRect = element.getBoundingClientRect();

      let topPosition = highlightRect.bottom + POPOVER_BOTTOM_PLACEMENT_OFFSET;
      if ((highlightRect.bottom + popoverElement.clientHeight + POPOVER_TOP_PLACEMENT_MARGE) > container.clientHeight) {
        topPosition =  highlightRect.top - POPOVER_BOTTOM_PLACEMENT_OFFSET - popoverElement.clientHeight;
      }

      setStyle({
        top: topPosition,
        left: highlightRect.left,
      });
    }
  }, [highlightRef]);

  if (isOpen) {
    classList.push("AknSpellCheck-popover--visible");
  }

  return (
    <>
      {isOpen && (
        <div ref={popoverRef}
             className={classList.join(' ')}
             style={style}
             onMouseEnter={handleMouseEnter}
             onMouseLeave={handleMouseLeave}>
          <PopoverContent mistake={mistake} widgetId={widgetId}/>
        </div>
      )}
    </>
  );
};

export default SpellCheckerPopover;
