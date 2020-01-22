import React, {ComponentType, FunctionComponent, useEffect, useState} from "react";
import {createPortal} from "react-dom";
import {WidgetProps} from "./Widget";

interface WidgetWithPortalDecoratorProps {
  containerId: string
}

const WidgetWithPortalDecorator = <P extends WidgetProps>(WidgetComponent:  ComponentType<P>): FunctionComponent<WidgetWithPortalDecoratorProps & P> => {
  return (props) => {
    const {containerId} = props;
    const [widgetRootElement, setWidgetRootElement] = useState();

    useEffect(() => {
      const element = document.createElement("div");
      element.id = containerId;
      setWidgetRootElement(element);

      document.body.prepend(element);

      return () => {
        document.body.removeChild(element);
      };
    }, []);

    return (
      <>
        {widgetRootElement && createPortal(<WidgetComponent {...props as P}/>, widgetRootElement)}
      </>
    );
  }
};

export default WidgetWithPortalDecorator;
