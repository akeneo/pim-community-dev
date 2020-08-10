import React, {ComponentType, FunctionComponent} from "react";
import {useFetchTitleSuggestion} from "../../../../../../../infrastructure/hooks";
import {WidgetProps} from "../Widget";

interface WidgetWithSuggestedTitleDecoratorProps {}

const WidgetWithSuggestedTitleDecorator = <P extends WidgetProps>(WidgetComponent:  ComponentType<P>): FunctionComponent<WidgetWithSuggestedTitleDecoratorProps & P> => {
  return (props) => {
    const {widget} = props;
    useFetchTitleSuggestion(widget);

    return (
      <WidgetComponent {...props as P}/>
    );
  }
};

export default WidgetWithSuggestedTitleDecorator;
