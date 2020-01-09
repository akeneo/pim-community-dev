import React, { FunctionComponent } from "react";
import HighlightsContainer from "./HighlightsContainer";
import {WidgetElement} from "../../../../../../domain";

interface WidgetProps {
  widget: WidgetElement
}

const Widget: FunctionComponent<WidgetProps> = ({ widget }) => {
  const {isVisible}  = widget;

  return (
    <>
      {isVisible && (
        <div className="AknSpellCheck-wrapper">
          <HighlightsContainer widget={widget}/>
        </div>
      )}
    </>
  );
};

export default Widget;
