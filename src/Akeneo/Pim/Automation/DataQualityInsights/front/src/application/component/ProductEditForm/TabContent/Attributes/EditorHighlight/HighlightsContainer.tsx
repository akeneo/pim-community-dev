import React, {FunctionComponent} from 'react';
import {WidgetElement} from '../../../../../helper';
import {useGetEditorHighlightBoundingRect} from '../../../../../../infrastructure/hooks';
import TextHighlightsWrapper from './TextHighlightsWrapper';

const defaultStyle = {
  top: 0,
  left: 0,
  width: 0,
  height: 0,
};

const computeStyle = (domRect: DOMRect) => {
  if (!domRect) {
    return defaultStyle;
  }

  return {
    top: domRect.y,
    left: domRect.x,
    width: domRect.width,
    height: domRect.height,
  };
};

interface HighlightsContainerProps {
  widget: WidgetElement;
}

const HighlightsContainer: FunctionComponent<HighlightsContainerProps> = ({widget}) => {
  const {isTextArea, isTextInput, isEditableContent, editor} = widget;
  const {editorBoundingClientRect} = useGetEditorHighlightBoundingRect(editor);

  return (
    <div
      className="AknEditorHighlight-highlights AknEditorHighlight--box-reset"
      style={computeStyle(editorBoundingClientRect)}
    >
      {(isTextArea || isTextInput || isEditableContent) && (
        <TextHighlightsWrapper widget={widget} editorBoundingClientRect={editorBoundingClientRect} />
      )}
    </div>
  );
};

export default HighlightsContainer;
