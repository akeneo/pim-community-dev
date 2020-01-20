import React, {useEffect, useLayoutEffect, useRef, useState} from "react";
import {useGetSpellcheckPopover} from "../../../../../infrastructure/hooks";
import PopoverContent from "./Spellchecker/PopoverContent";
import {SpellcheckPopoverContextProvider} from "../../../../../infrastructure/context-provider";

const POPOVER_BOTTOM_PLACEMENT_OFFSET = 2;
const POPOVER_LEFT_PLACEMENT_OFFSET = 0;
const POPOVER_TOP_PLACEMENT_MARGE = 20;
const POPOVER_RIGHT_PLACEMENT_MARGE = 20;
const PEF_CONTAINER_SELECTOR = '.entity-edit-form.edit-form';

interface PopoverStyleState {
  left?: number;
  top?: number;
}

const SpellCheckerPopover = () => {
  const {isOpen, highlight, widgetId, handleOpening, handleClosing} = useGetSpellcheckPopover();
  const [style, setStyle] = useState<PopoverStyleState>({});
  const popoverRef = useRef<HTMLDivElement>(null);
  const classList = ["AknSpellCheck-popover"];

  if (isOpen) {
    classList.push("AknSpellCheck-popover--visible");
  }

  useLayoutEffect(() => {
    const element = highlight && highlight.domRange ? highlight.domRange : null;
    const container = document.querySelector(PEF_CONTAINER_SELECTOR);
    const popoverElement = popoverRef && popoverRef.current ? popoverRef.current : null;

    if (element && container && popoverElement) {
      const highlightRect = element.getBoundingClientRect();

      let topPosition = highlightRect.bottom + POPOVER_BOTTOM_PLACEMENT_OFFSET;
      if ((highlightRect.bottom + popoverElement.clientHeight + POPOVER_TOP_PLACEMENT_MARGE) > container.clientHeight) {
        topPosition =  highlightRect.top - POPOVER_BOTTOM_PLACEMENT_OFFSET - popoverElement.clientHeight;
      }

      let leftPosition = highlightRect.left + POPOVER_LEFT_PLACEMENT_OFFSET;
      if ((highlightRect.left + popoverElement.clientWidth + POPOVER_RIGHT_PLACEMENT_MARGE) > container.clientWidth) {
        leftPosition =  highlightRect.right - POPOVER_LEFT_PLACEMENT_OFFSET - popoverElement.clientWidth;
      }

      setStyle({
        top: topPosition,
        left: leftPosition,
      });
    }
  }, [highlight]);

  useEffect(() => {
    return () => {
      handleClosing()
    };
  }, []);

  return (
    <>
      <SpellcheckPopoverContextProvider popoverRef={popoverRef} widgetId={widgetId} isOpen={isOpen} handleClosing={handleClosing}/>
      {isOpen && (
        <div ref={popoverRef}
             className={classList.join(' ')}
             style={style}
             onMouseEnter={() => handleOpening(widgetId, highlight)}
             onMouseLeave={() => handleClosing()}>
          {highlight && highlight.mistake && <PopoverContent mistake={highlight.mistake} widgetId={widgetId}/>}
        </div>
      )}
    </>
  );
};

export default SpellCheckerPopover;
