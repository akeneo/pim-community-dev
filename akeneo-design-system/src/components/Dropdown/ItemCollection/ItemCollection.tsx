import {Key, Override} from '../../../shared';
import React, {ReactNode, useRef, Ref, useCallback, KeyboardEvent} from 'react';
import styled from 'styled-components';
import {useAutoFocus} from 'hooks';

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
    const firstItemRef = useRef<HTMLDivElement>(null);
    const handleKeyDown = useCallback((event: KeyboardEvent<HTMLDivElement>) => {
      if (null !== event.currentTarget) {
        if (event.key === Key.ArrowDown) {
          ((event.currentTarget as HTMLElement).nextSibling as HTMLElement)?.focus();
          event.preventDefault();
        }
        if (event.key === Key.ArrowUp) {
          ((event.currentTarget as HTMLElement).previousSibling as HTMLElement)?.focus();
          event.preventDefault();
        }
      }
    }, []);
    const decoratedChildren = React.Children.map(children, (child, index) => {
      if (React.isValidElement(child)) {
        return React.cloneElement(child, {
          ref: 0 === index ? firstItemRef : undefined,
          onKeyDown: handleKeyDown,
        });
      }

      return child;
    });
    useAutoFocus(firstItemRef);

    return (
      <ItemCollectionContainer {...rest} ref={forwardedRef}>
        {decoratedChildren}
      </ItemCollectionContainer>
    );
  }
);

export {ItemCollection};
