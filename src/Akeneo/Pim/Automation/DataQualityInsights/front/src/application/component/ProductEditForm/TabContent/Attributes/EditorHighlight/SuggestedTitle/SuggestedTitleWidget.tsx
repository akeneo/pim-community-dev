import {FunctionComponent} from "react";
import Widget, {WidgetProps} from "../Widget";
import WidgetWithSuggestedTitleDecorator from "./WidgetWithSuggestedTitleDecorator";

interface SuggestedTitleWidgetProps extends WidgetProps {}

const SuggestedTitleWidget: FunctionComponent<SuggestedTitleWidgetProps> = (props) => {
  return WidgetWithSuggestedTitleDecorator(Widget)({
    ...props
  });
};

export default SuggestedTitleWidget;
