import {Key, Override} from '../../../shared';
import React, {ReactNode, Children, useRef, useCallback, KeyboardEvent, isValidElement, cloneElement} from 'react';
import styled from 'styled-components';
import {useAutoFocus, useCombinedRefs} from '../../../hooks';
import {usePagination} from '../../../hooks/usePagination';
import {GroupsIllustration} from '../../../illustrations';
import {getColor, getFontSize} from '../../../theme';

const ItemCollectionContainer = styled.div`
  max-height: 320px;
  overflow-y: auto;
  overflow-x: hidden;
`;

const NoResultSection = styled.div`
  text-align: center;
  margin: 0 30px 10px 30px;
`;

const NoResultTitle = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('default')};
  text-align: center;
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
    children?: ReactNode;

    /**
     * The text displayed when no result was found.
     */
    noResultLabel?: string;
  }
>;

const ItemCollection = React.forwardRef<HTMLDivElement, ItemCollectionProps>(
  ({children, onNextPage, noResultLabel, ...rest}: ItemCollectionProps, forwardedRef) => {
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

    usePagination(containerRef, lastItemRef, onNextPage, true);
    useAutoFocus(firstItemRef);

    return (
      <ItemCollectionContainer {...rest} ref={containerRef}>
        {childrenCount
          ? decoratedChildren
          : noResultLabel && (
              <NoResultSection>
                <GroupsIllustration size={128} />
                <NoResultTitle>{noResultLabel}</NoResultTitle>
              </NoResultSection>
            )}
      </ItemCollectionContainer>
    );
  }
);

export {ItemCollection};
