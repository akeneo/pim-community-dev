import React, {FunctionComponent} from "react";
import TextAreaHighlightsWrapper from "./TextAreaHighlightsWrapper";
import {WidgetElement} from "../../../../../../../domain";

interface HighlightsWrapperFactoryProps {
  widget: WidgetElement;
  editorBoundingClientRect: DOMRect;
}

const HighlightsWrapperFactory: FunctionComponent<HighlightsWrapperFactoryProps> = ({ widget, editorBoundingClientRect }) => {
  const { isTextArea } = widget;

  return (
    <>
      {isTextArea && (
        <TextAreaHighlightsWrapper
          widget={widget}
          editorBoundingClientRect={editorBoundingClientRect}
        />
      )}
    </>
  );
};

export default HighlightsWrapperFactory;
