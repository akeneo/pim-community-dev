import React, {FunctionComponent, useLayoutEffect, useRef} from "react";
import Highlight from "./Highlight";
import {WidgetElement} from "../../../../../../../domain";
import {useGetSpellcheckEditorScroll, useGetSpellcheckHighlights} from "../../../../../../../infrastructure/hooks";


interface TextAreaHighlightsWrapperProps {
  widget: WidgetElement;
  editorBoundingClientRect: DOMRect;
}

const TextAreaHighlightsWrapper: FunctionComponent<TextAreaHighlightsWrapperProps> = ({
  widget,
  editorBoundingClientRect,
}) => {
  // @info: Add a blank line ath the content end to fix the scroll height issue on the cloned editor
  const content = `${widget.content}\n`;
  const wrapperStyle = {
    width: editorBoundingClientRect.width,
    height: editorBoundingClientRect.height
  };
  const clonedEditorRef = useRef<HTMLDivElement>(null);
  const {editorScrollTop, editorScrollLeft} = useGetSpellcheckEditorScroll(widget.editor);
  const highlights = useGetSpellcheckHighlights(widget, clonedEditorRef.current);

  useLayoutEffect(() => {
    const element = clonedEditorRef.current;

    if (element) {
      element.scrollTop = editorScrollTop;
      element.scrollLeft = editorScrollLeft;
    }
  }, [editorScrollTop, editorScrollLeft]);

  return (
    <>
      <div
        className="AknSpellCheck-highlights-wrapper AknSpellCheck-highlights-wrapper--textarea"
        style={wrapperStyle}
      >
        {highlights.length > 0 && highlights.map((highlight, index) => (
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
