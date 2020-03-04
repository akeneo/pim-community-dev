import {FunctionComponent} from "react";
import Widget, {WidgetProps} from "../Widget";
import WidgetWithSuggestedTitleDecorator from "./WidgetWithSuggestedTitleDecorator";

interface SuggestedTitleWidgetProps extends WidgetProps {}

const WIDGET_NAME = "suggested-title";

const SuggestedTitleWidget: FunctionComponent<SuggestedTitleWidgetProps> = (props) => {
  return WidgetWithSuggestedTitleDecorator(Widget)({
    ...props,
    name: WIDGET_NAME
  });
};

export default SuggestedTitleWidget;
