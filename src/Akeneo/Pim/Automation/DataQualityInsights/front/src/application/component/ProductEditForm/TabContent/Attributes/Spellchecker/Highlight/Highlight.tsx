import React, {FunctionComponent, useEffect, useLayoutEffect, useState} from "react";
import {HighlightElement} from "../../../../../../../domain";

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
//const HIGHLIGHT_HIDDEN_CLASSNAME = "AknSpellCheck-mark--hidden";
const HIGHLIGHT_CLASSNAME = "AknSpellCheck-mark";

interface HighlightPros {
  highlight: HighlightElement;
  editorRect: DOMRect;
  content: string
}
const Highlight: FunctionComponent<HighlightPros> = ({ highlight, editorRect, content}) => {
  const [highlightRect, setHighlightRect] = useState<DOMRect | null>(null);
  const [classList, setClassList] = useState<string []>([]);

  useEffect(() => {
    setClassList([...highlight.classList, HIGHLIGHT_CLASSNAME]);
  }, [highlight.classList]);

  useLayoutEffect(() => {
    (async () => {
      const domRect = highlight.domRange.getBoundingClientRect();
      setHighlightRect(domRect);
    })();
  }, [highlight.domRange, editorRect, content]);

  const handleMouseOver = () => {
    setClassList([
      ...highlight.classList,
      HIGHLIGHT_HOVER_CLASSNAME,
      HIGHLIGHT_CLASSNAME
    ]);
  };

  const handleMouseOut = () => {
    setClassList([...highlight.classList, HIGHLIGHT_CLASSNAME]);
  };

  return (
    <div
      className={classList.join(" ")}
      style={computeStyle(highlightRect, editorRect)}
      onMouseOver={handleMouseOver}
      onMouseOut={handleMouseOut}
    />
  );
};

export default Highlight;
