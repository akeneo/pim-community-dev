import {FunctionComponent} from "react";
import Widget, {WidgetProps} from "../Widget";
import WidgetWithSpellcheckDecorator from "./WidgetWithSpellcheckDecorator";

interface SpellcheckWidgetProps extends WidgetProps {}

const SpellcheckWidget: FunctionComponent<SpellcheckWidgetProps> = (props) => {
  return WidgetWithSpellcheckDecorator(Widget)({
    ...props
  });
};

export default SpellcheckWidget;
