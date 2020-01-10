import React, {FunctionComponent, useCallback, useEffect, useLayoutEffect, useRef, useState} from "react";
import {HighlightElement} from "../../../../../../../domain";
import {useGetSpellcheckPopover} from "../../../../../../../infrastructure/hooks";

interface Style {
  top: number;
  left: number;
  width: number;
  height: number;
}

const defaultStyle: Style = {
  top: 0,
  left: 0,
  width: 0,
  height: 0
};

const computeStyle = (domRect: DOMRect | null, editorRect: DOMRect) => {
  if (!domRect) {
    return defaultStyle;
  }
  return {
    top: domRect.y - editorRect.y,
    left: domRect.x - editorRect.x,
    width: domRect.width,
    height: domRect.height
  };
};

const HIGHLIGHT_HOVER_CLASSNAME = "AknSpellCheck-mark--hover";
const HIGHLIGHT_CLASSNAME = "AknSpellCheck-mark";

interface HighlightPros {
  highlight: HighlightElement;
  editorRect: DOMRect;
  content: string;
  widgetId: string;
}
const Highlight: FunctionComponent<HighlightPros> = ({ highlight, editorRect, content, widgetId}) => {
  const [highlightRect, setHighlightRect] = useState<DOMRect | null>(null);
  const [classList, setClassList] = useState<string []>([]);
  const highlightRef = useRef<HTMLDivElement>(null);
  const {handleOpening, handleClosing} = useGetSpellcheckPopover();

  useEffect(() => {
    setClassList([...highlight.classList, HIGHLIGHT_CLASSNAME]);
  }, [highlight.classList]);

  useLayoutEffect(() => {
    (async () => {
      const domRect = highlight.domRange.getBoundingClientRect();
      setHighlightRect(domRect);
    })();
  }, [highlight.domRange, editorRect, content]);

  const handleMouseOver = useCallback(() => {
    handleOpening(widgetId, highlight.mistake, highlightRef, () => {
      setClassList([
        ...highlight.classList,
        HIGHLIGHT_HOVER_CLASSNAME,
        HIGHLIGHT_CLASSNAME
      ]);
    });
  }, [handleClosing, highlight, widgetId]);

  const handleMouseOut = useCallback(() => {
    handleClosing(() => {
      setClassList([...highlight.classList, HIGHLIGHT_CLASSNAME]);
    });
  }, [handleClosing, highlight]);

  return (
    <div
      ref={highlightRef}
      className={classList.join(" ")}
      style={computeStyle(highlightRect, editorRect)}
      onMouseOver={handleMouseOver}
      onMouseOut={handleMouseOut}
    />
  );
};

export default Highlight;
