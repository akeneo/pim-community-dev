import {Key, Override} from '../../../shared';
import React, {ReactNode, useRef, Ref, useCallback} from 'react';
import styled from 'styled-components';
import {useAutoFocus} from 'hooks';
import {Link} from '../../../components';

const decorateChildren = (children: ReactNode) => {
  const firstItemRef = useRef<HTMLDivElement>(null);
  const handleKeyDown = useCallback((event: KeyboardEvent) => {
    if (null === event.currentTarget) return;

    if (event.key === Key.ArrowDown) {
      (event.currentTarget as any)?.nextSibling.focus();
      event.preventDefault();
    }
    if (event.key === Key.ArrowUp) {
      (event.currentTarget as any)?.previousSibling.focus();
      event.preventDefault();
    }
  }, []);
  const decoratedChildren = React.Children.map(children, (child, index) => {
    if (!React.isValidElement(child)) return child;

    return React.cloneElement(child, {
      decorated: child.type === Link ? false : undefined,
      ref: 0 === index ? firstItemRef : undefined,
      onKeyDown: handleKeyDown,
    });
  });
  useAutoFocus(firstItemRef);

  return decoratedChildren;
};

const ItemCollectionContainer = styled.div`
  max-height: 320px;
  overflow-y: auto;
  overflow-x: hidden;
`;

type ItemCollectionProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    children: ReactNode;
  }
>;

const ItemCollection = React.forwardRef<HTMLDivElement, ItemCollectionProps>(
  ({children, ...rest}: ItemCollectionProps, forwardedRef: Ref<HTMLDivElement>): React.ReactElement => {
    const decoratedChildren = decorateChildren(children);

    return (
      <ItemCollectionContainer {...rest} ref={forwardedRef}>
        {decoratedChildren}
      </ItemCollectionContainer>
    );
  }
);

export {ItemCollection};
