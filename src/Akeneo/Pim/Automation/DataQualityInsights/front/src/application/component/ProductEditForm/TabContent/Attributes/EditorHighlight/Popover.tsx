import React, {FunctionComponent, useEffect, useLayoutEffect, useRef, useState} from "react";
import {useGetEditorHighlightPopover, useGetSpellcheckWidget} from "../../../../../../infrastructure/hooks";
import {EditorHighlightPopoverContextListener} from "../../../../../listener";
import SpellcheckPopoverContent from "./Spellcheck/SpellcheckPopoverContent";
import SuggestedTitlePopoverContent from "./SuggestedTitle/SuggestedTitlePopoverContent";
import PopoverWithPortalDecorator from "./PopoverWithPortalDecorator";

const POPOVER_BOTTOM_PLACEMENT_OFFSET = 2;
const POPOVER_LEFT_PLACEMENT_OFFSET = 0;
const POPOVER_TOP_PLACEMENT_MARGE = 20;
const POPOVER_RIGHT_PLACEMENT_MARGE = 20;
const PEF_CONTAINER_SELECTOR = '.entity-edit-form.edit-form';
const CONTAINER_ELEMENT_ID = 'akeneo-spellchecker-popover-root';

interface PopoverStyleState {
  left?: number;
  top?: number;
}

export interface  PopoverProps {}

const BasePopover: FunctionComponent<PopoverProps> = () => {
  const {isOpen, highlight, widgetId, handleOpening, handleClosing} = useGetEditorHighlightPopover();
  const widget = useGetSpellcheckWidget(widgetId);
  const [style, setStyle] = useState<PopoverStyleState>({});
  const popoverRef = useRef<HTMLDivElement>(null);
  const classList = ["AknEditorHighlight-popover"];

  if (isOpen) {
    classList.push("AknEditorHighlight-popover--visible");
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
      <EditorHighlightPopoverContextListener popoverRef={popoverRef} activeHighlight={highlight} handleClosing={handleClosing}/>
      {isOpen && (
        <div ref={popoverRef}
             className={classList.join(' ')}
             style={style}
             onMouseEnter={() => handleOpening(widget, highlight)}
             onMouseLeave={() => handleClosing()}>
          {highlight && highlight.mistake && widget && (
            <>
              {widget.isMainLabel ?
                <SuggestedTitlePopoverContent mistake={highlight.mistake} widget={widget}/> :
                <SpellcheckPopoverContent mistake={highlight.mistake} widget={widget}/>
              }
            </>
          )}
        </div>
      )}
    </>
  );
};

const Popover: FunctionComponent<PopoverProps> = (props) => {
  return PopoverWithPortalDecorator(BasePopover)({
    ...props,
    containerId: CONTAINER_ELEMENT_ID
  });
};

export default Popover;
