import React, {FunctionComponent, useCallback, useEffect, useLayoutEffect, useRef} from "react";
import {throttle} from "lodash";
import Highlight from "./Highlight";
import {HighlightElement, WidgetElement} from "../../../../../../../domain";
import {
  useGetSpellcheckEditorScroll,
  useGetSpellcheckHighlights,
  useGetSpellcheckPopover
} from "../../../../../../../infrastructure/hooks";

const MOVING_MILLISECONDS_DELAY = 100;

const HIGHLIGHT_INTERSECTING_MARGIN = 2;

const isIntersectingHighlight = (x: number, y: number, highlight: HighlightElement) => {
  const rect: DOMRect = highlight.domRange.getBoundingClientRect();

  return (
    x >= (rect.left - HIGHLIGHT_INTERSECTING_MARGIN) &&
    x <= (rect.right + HIGHLIGHT_INTERSECTING_MARGIN) &&
    y >= (rect.top - HIGHLIGHT_INTERSECTING_MARGIN) &&
    y <= (rect.bottom + HIGHLIGHT_INTERSECTING_MARGIN)
  );
};

interface TextAreaHighlightsWrapperProps {
  widget: WidgetElement;
  editorBoundingClientRect: DOMRect;
}

const TextAreaHighlightsWrapper: FunctionComponent<TextAreaHighlightsWrapperProps> = ({
  widget,
  editorBoundingClientRect,
}) => {
  // @info: Add a couple of blank lines at the content end to fix the scroll height issue with the cloned editor
  const content = `${widget.content}\n\n`;
  const wrapperStyle = {
    width: editorBoundingClientRect.width,
    height: editorBoundingClientRect.height
  };
  const clonedEditorRef = useRef<HTMLDivElement>(null);
  const {editorScrollTop, editorScrollLeft} = useGetSpellcheckEditorScroll(widget.editor);
  const highlights = useGetSpellcheckHighlights(widget, clonedEditorRef.current);
  const {handleOpening, handleClosing} = useGetSpellcheckPopover();

  const handleMouseMove = useCallback(throttle((event: React.MouseEvent) => {
    window.requestAnimationFrame(() => {
      const activeHighlight = Object.values(highlights).find((highlight) =>  {
        return isIntersectingHighlight(event.clientX, event.clientY, highlight)
      });

      if (!activeHighlight) {
        handleClosing();
        return;
      }

      if (!activeHighlight.isActive) {
        handleOpening(widget.id, activeHighlight);
      }
    })
  }, MOVING_MILLISECONDS_DELAY), [highlights]);

  useLayoutEffect(() => {
    const element = clonedEditorRef.current;

    if (element) {
      element.scrollTop = editorScrollTop;
      element.scrollLeft = editorScrollLeft;
    }
  }, [editorScrollTop, editorScrollLeft]);

  useEffect(() => {
    const {editor} = widget;

    if (editor && handleMouseMove) {
      // @ts-ignore
      editor.addEventListener('mousemove', handleMouseMove);
    }
    return () => {
      if (editor && handleMouseMove) {
        // @ts-ignore
        editor.removeEventListener('mousemove', handleMouseMove);
      }
    };
  }, [widget.editor, widget.editorId, highlights, handleMouseMove]);

  return (
    <>
      <div
        className="AknSpellCheck-highlights-wrapper AknSpellCheck-highlights-wrapper--textarea"
        style={wrapperStyle}
      >
        {Object.values(highlights).map((highlight, index) => (
          <Highlight
            key={`highlight-${widget.id}-${index}`}
            highlight={highlight}
            editorRect={editorBoundingClientRect}
            content={widget.content}
          />
        ))}
      </div>
      <div ref={clonedEditorRef} className="AknSpellCheck-cloned-editor" aria-hidden={true} style={wrapperStyle}>{content}</div>
    </>
  );
};
export default TextAreaHighlightsWrapper;
