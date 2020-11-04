import {FunctionComponent} from 'react';
import Widget, {WidgetProps} from '../Widget';
import WidgetWithSpellcheckDecorator from './WidgetWithSpellcheckDecorator';

interface SpellcheckWidgetProps extends WidgetProps {}

const WIDGET_NAME = 'spellcheck';

const SpellcheckWidget: FunctionComponent<SpellcheckWidgetProps> = props => {
  return WidgetWithSpellcheckDecorator(Widget)({
    ...props,
    name: WIDGET_NAME,
  });
};

export default SpellcheckWidget;
