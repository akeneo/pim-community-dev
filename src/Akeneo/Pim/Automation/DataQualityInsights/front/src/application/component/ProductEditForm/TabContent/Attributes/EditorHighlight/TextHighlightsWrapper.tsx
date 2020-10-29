import React, {FunctionComponent, useLayoutEffect, useRef} from 'react';
import Highlight from './Highlight';
import {getEditorType, WidgetElement} from '../../../../../helper';
import {useGetEditorHighlights, useGetEditorHighlightScroll} from '../../../../../../infrastructure/hooks';
import {EditorContextListener} from '../../../../../listener';

interface TextHighlightsWrapperProps {
  widget: WidgetElement;
  editorBoundingClientRect: DOMRect;
}

const TextHighlightsWrapper: FunctionComponent<TextHighlightsWrapperProps> = ({widget, editorBoundingClientRect}) => {
  // @info: Add a couple of blank lines at the content end to fix the scroll height issue with the cloned editor
  const content = `${widget.content}\n\n`;
  const editorType = getEditorType(widget);
  const wrapperStyle = {
    width: editorBoundingClientRect.width,
    height: editorBoundingClientRect.height,
  };
  const clonedEditorRef = useRef<HTMLDivElement>(null);
  const {editorScrollTop, editorScrollLeft} = useGetEditorHighlightScroll(widget.editor);
  const highlights = useGetEditorHighlights(
    widget,
    widget.isTextArea || widget.isTextInput ? clonedEditorRef.current : null
  );

  useLayoutEffect(() => {
    const element = clonedEditorRef.current;

    if (element) {
      element.scrollTop = editorScrollTop;
      element.scrollLeft = editorScrollLeft;
    }
  }, [editorScrollTop, editorScrollLeft]);

  return (
    <>
      <EditorContextListener widget={widget} highlights={highlights} />
      <div
        className={`AknEditorHighlight-highlights-wrapper AknEditorHighlight-highlights-wrapper--${editorType}`}
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
      {(widget.isTextArea || widget.isTextInput) && (
        <div
          ref={clonedEditorRef}
          className={`AknEditorHighlight-cloned-editor AknEditorHighlight-cloned-editor--${editorType}`}
          aria-hidden={true}
          style={wrapperStyle}
        >
          {content}
        </div>
      )}
    </>
  );
};

export default TextHighlightsWrapper;
