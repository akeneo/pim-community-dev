import React, {FunctionComponent, useLayoutEffect, useRef} from "react";
import Highlight from "./Highlight";
import {WidgetElement} from "../../../../../../../domain";
import {useGetSpellcheckEditorScroll, useGetSpellcheckHighlights} from "../../../../../../../infrastructure/hooks";
import {EditorContextProvider} from "../../../../../../../infrastructure/context-provider";


enum EditorTypes {
  TEXT = 'text',
  TEXTAREA = 'textarea',
  RICHTEXT = 'richtext',
  UNKNOWN = 'unknown',
}


const getEditorType = (widget: WidgetElement) => {
  if (widget.isTextArea) {
    return EditorTypes.TEXTAREA;
  }

  if (widget.isTextInput) {
    return EditorTypes.TEXT;
  }

  return EditorTypes.UNKNOWN;
};

interface TextHighlightsWrapperProps {
  widget: WidgetElement;
  editorBoundingClientRect: DOMRect;
}

const TextHighlightsWrapper: FunctionComponent<TextHighlightsWrapperProps> = ({
  widget,
  editorBoundingClientRect,
}) => {
  // @info: Add a couple of blank lines at the content end to fix the scroll height issue with the cloned editor
  const content = `${widget.content}\n\n`;
  const editorType = getEditorType(widget);
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
      <EditorContextProvider widget={widget} highlights={highlights} />
      <div
        className={`AknSpellCheck-highlights-wrapper AknSpellCheck-highlights-wrapper--${editorType}`}
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
      <div ref={clonedEditorRef} className={`AknSpellCheck-cloned-editor AknSpellCheck-cloned-editor--${editorType}`} aria-hidden={true} style={wrapperStyle}>{content}</div>
    </>
  );
};
export default TextHighlightsWrapper;
