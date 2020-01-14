import React, {FunctionComponent} from "react";
import {WidgetElement} from "../../../../../../domain";
import {useGetSpellcheckEditorBoundingRect} from "../../../../../../infrastructure/hooks";
import TextAreaHighlightsWrapper from "./Highlight/TextAreaHighlightsWrapper";

const defaultStyle = {
  top: 0,
  left: 0,
  width: 0,
  height: 0
};

const computeStyle = (domRect: DOMRect) => {
  if (!domRect) {
    return defaultStyle;
  }

  return {
    top: domRect.y,
    left: domRect.x,
    width: domRect.width,
    height: domRect.height
  };
};

interface HighlightsContainerProps {
  widget: WidgetElement;
}

const HighlightsContainer: FunctionComponent<HighlightsContainerProps> = ({ widget }) => {
  const { isTextArea, editor } = widget;
  const { editorBoundingClientRect } = useGetSpellcheckEditorBoundingRect(editor);

  return (
    <div
      className="AknSpellCheck-highlights AknSpellCheck--box-reset"
      style={computeStyle(editorBoundingClientRect)}
    >
      {isTextArea && (
        <TextAreaHighlightsWrapper
          widget={widget}
          editorBoundingClientRect={editorBoundingClientRect}
        />
      )}
    </div>
  );
};

export default HighlightsContainer;
