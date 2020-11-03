import React, {ComponentType, FC, useEffect, useState} from 'react';
import {createPortal} from 'react-dom';

type PortalProps = {
  rootElement: Element;
  containerId: string;
};

export const withPortal = <P extends object>(WrappedContainer: ComponentType<P>): FC<P & PortalProps> => {
  return props => {
    const {rootElement, containerId} = props;
    const [container, setContainer] = useState<HTMLDivElement>();

    useEffect(() => {
      const element = document.createElement('div');
      element.id = containerId;
      element.setAttribute('data-testid', containerId);

      setContainer(element);

      rootElement.prepend(element);

      return () => {
        rootElement.removeChild(element);
      };
    }, []);

    return <>{container && createPortal(<WrappedContainer {...props} />, container)}</>;
  };
};

export default withPortal;
