import React, {ComponentType, FunctionComponent, useEffect, useState} from 'react';
import {createPortal} from 'react-dom';
import {PopoverProps} from './Popover';

interface PopoverWithPortalDecoratorProps {
  containerId: string;
}

const PopoverWithPortalDecorator = <P extends PopoverProps>(
  PopoverComponent: ComponentType<P>
): FunctionComponent<PopoverWithPortalDecoratorProps & P> => {
  return props => {
    const {containerId} = props;
    const [popoverContainer, setPopoverContainer] = useState<Element | null>(null);

    useEffect(() => {
      const element = document.createElement('div');
      element.id = containerId;
      setPopoverContainer(element);

      document.body.appendChild(element);

      return () => {
        document.body.removeChild(element);
      };
    }, []);

    return <>{popoverContainer && createPortal(<PopoverComponent {...(props as P)} />, popoverContainer)}</>;
  };
};

export default PopoverWithPortalDecorator;
