import {Key, Override} from '../../../shared';
import React, {
  ReactNode,
  Children,
  useRef,
  useCallback,
  KeyboardEvent,
  useEffect,
  isValidElement,
  cloneElement,
} from 'react';
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
    const containerRef = useCombinedRefs(forwardedRef);

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

    const childrenCount = Children.toArray(children).filter(isValidElement).length;

    const decoratedChildren = Children.map(children, (child, index) => {
      if (isValidElement(child)) {
        return cloneElement(child, {
          ref: 0 === index ? firstItemRef : index === childrenCount - 1 ? lastItemRef : undefined,
          onKeyDown: handleKeyDown,
        });
      }

      return child;
    });

    useEffect(() => {
      const containerElement = containerRef.current;
      const lastElement = lastItemRef.current;
      if (
        undefined === onNextPage ||
        null === containerElement ||
        null === lastItemRef.current ||
        null === lastElement
      ) {
        return;
      }

      const options = {
        root: containerElement,
        rootMargin: '0px 0px 100% 0px',
        threshold: 0,
      };

      const observer = new IntersectionObserver((entries: IntersectionObserverEntry[]) => {
        if (entries[0].isIntersecting) {
          onNextPage();
        }
      }, options);

      observer.observe(lastElement);

      return () => observer.unobserve(lastElement);
    }, [onNextPage]);

    useAutoFocus(firstItemRef);

    return (
      <ItemCollectionContainer {...rest} ref={containerRef}>
        {decoratedChildren}
      </ItemCollectionContainer>
    );
  }
);

export {ItemCollection};
