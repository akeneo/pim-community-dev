import {Key, Override} from '../../../shared';
import React, {
  ReactNode,
  Children,
  useRef,
  useCallback,
  KeyboardEvent,
  isValidElement,
  cloneElement,
  ReactElement,
} from 'react';
import styled from 'styled-components';
import {useAutoFocus, useCombinedRefs} from '../../../hooks';
import {usePagination} from '../../../hooks/usePagination';
import {Placeholder} from '../../Placeholder/Placeholder';
import {IllustrationProps} from '../../../illustrations/IllustrationProps';
import {getFontSize} from '../../../theme';

const ItemCollectionContainer = styled.div`
  max-height: 320px;
  overflow-y: auto;
  overflow-x: hidden;
`;

const NoResultPlaceholderContainer = styled(Placeholder)`
  margin: 10px 10px 20px 10px;
  & > div {
    font-size: ${getFontSize('default')};
  }
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
     * The illustration displayed when no result was found.
     */
    noResultIllustration?: ReactElement<IllustrationProps>;

    /**
     * The text displayed when no result was found.
     */
    noResultTitle?: string;
  }
>;

const ItemCollection: React.FC<ItemCollectionProps & {ref?: React.Ref<HTMLDivElement>}> = React.forwardRef<HTMLDivElement, ItemCollectionProps>(
  ({children, onNextPage, noResultTitle, noResultIllustration, ...rest}: ItemCollectionProps, forwardedRef) => {
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
        return cloneElement(child as any, {
          ref: 0 === index ? firstItemRef : index === childrenCount - 1 ? lastItemRef : undefined,
          onKeyDown: handleKeyDown,
        });
      }

      return child;
    });

    usePagination(containerRef, lastItemRef, onNextPage, true);
    useAutoFocus(firstItemRef);

    return (
      <ItemCollectionContainer role="listbox" {...rest} ref={containerRef}>
        {childrenCount
          ? decoratedChildren
          : noResultIllustration &&
            noResultTitle && <NoResultPlaceholderContainer illustration={noResultIllustration} title={noResultTitle} />}
      </ItemCollectionContainer>
    );
  }
);

export {ItemCollection};
