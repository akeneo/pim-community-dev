import React, {FunctionComponent} from "react";
import HighlightsContainer from "./HighlightsContainer";
import {WidgetElement} from "../../../../../helper";
import WidgetWithContextListenerDecorator from "./WidgetWithContextListenerDecorator";
import WidgetWithPortalDecorator from "./WidgetWithPortalDecorator";

const WIDGET_PREFIX_ID = "akeneo-spellchecker-widget";

export interface WidgetProps {
  widget: WidgetElement
}

const BaseWidget: FunctionComponent<WidgetProps> = ({ widget }) => {
  const {isVisible}  = widget;

  return (
    <>
      {isVisible && (
        <div className="AknEditorHighlight-wrapper">
          <HighlightsContainer widget={widget}/>
        </div>
      )}
    </>
  );
};

const WidgetWithContextListener: FunctionComponent<WidgetProps> = (props) => {
  return WidgetWithContextListenerDecorator(BaseWidget)({
    ...props
  });
};

const Widget: FunctionComponent<WidgetProps> = (props) => {
  return WidgetWithPortalDecorator(WidgetWithContextListener)({
    ...props,
    containerId: `${WIDGET_PREFIX_ID}-${props.widget.id}`,
  });
};

export default Widget;
