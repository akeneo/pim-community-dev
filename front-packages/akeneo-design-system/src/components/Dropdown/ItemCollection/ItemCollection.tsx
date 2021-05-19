import {Key, Override} from '../../../shared';
import React, {ReactNode, Children, useRef, useCallback, KeyboardEvent, useEffect} from 'react';
import styled from 'styled-components';
import {useAutoFocus, useCombinedRefs} from '../../../hooks';

const ItemCollectionContainer = styled.div`
  max-height: 320px;
  overflow-y: auto;
  overflow-x: hidden;
`;

type ItemCollectionProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Handler called when the next page is almost reached.
     */
    onNextPage?: () => void;

    /**
     * The list of items.
     */
    children: ReactNode;
  }
>;

const ItemCollection = React.forwardRef<HTMLDivElement, ItemCollectionProps>(
  ({children, onNextPage, ...rest}: ItemCollectionProps, forwardedRef) => {
    const firstItemRef = useRef<HTMLDivElement>(null);
    const lastItemRef = useRef<HTMLDivElement>(null);

    const internalRef = useRef<HTMLDivElement>(null);
    const containerRef = useCombinedRefs(internalRef, forwardedRef);

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
          ref: 0 === index ? firstItemRef : index === Children.count(children) - 1 ? lastItemRef : undefined,
          onKeyDown: handleKeyDown,
        });
      }

      return child;
    });

    useEffect(() => {
      const containerElement = containerRef.current;
      const lastElement = lastItemRef.current;

      if (undefined === onNextPage || null === containerElement || null === lastItemRef.current) return;

      const options = {
        root: containerElement,
        rootMargin: '0px 0px 100% 0px',
        threshold: 1.0,
      };

      /* istanbul ignore next first render */
      if (null === lastElement) return;

      const observer = new IntersectionObserver((entries: IntersectionObserverEntry[]) => {
        if (entries[0].isIntersecting) onNextPage();
      }, options);

      observer.observe(lastElement);

      return () => {
        observer.unobserve(lastElement);
      };
    }, [onNextPage, containerRef.current, lastItemRef.current]);

    useAutoFocus(firstItemRef);

    return (
      <ItemCollectionContainer {...rest} ref={containerRef}>
        {decoratedChildren}
      </ItemCollectionContainer>
    );
  }
);

export {ItemCollection};
