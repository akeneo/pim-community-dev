import React, {FunctionComponent, useLayoutEffect, useRef, useState} from "react";
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
const HIGHLIGHT_CLASSNAME = "AknSpellCheck-mark";
const HIGHLIGHT_TYPE_CLASSNAME_PREFIX = "AknSpellCheck-mark--";

interface HighlightPros {
  highlight: HighlightElement;
  editorRect: DOMRect;
  content: string;
}

const Highlight: FunctionComponent<HighlightPros> = ({ highlight, editorRect, content}) => {
  const {isActive, domRange, mistake} = highlight;
  const [highlightRect, setHighlightRect] = useState<DOMRect | null>(null);
  const highlightRef = useRef<HTMLDivElement>(null);
  const classList = [
    HIGHLIGHT_CLASSNAME,
    `${HIGHLIGHT_TYPE_CLASSNAME_PREFIX}${mistake.type}`
  ];

  if (isActive) {
    classList.push(HIGHLIGHT_HOVER_CLASSNAME);
  }

  useLayoutEffect(() => {
    (async () => {
      const domRect = domRange.getBoundingClientRect();
      setHighlightRect(domRect);
    })();
  }, [domRange, editorRect, content]);


  return (
    <div
      ref={highlightRef}
      className={classList.join(" ")}
      style={computeStyle(highlightRect, editorRect)}
    />
  );
};

export default Highlight;
