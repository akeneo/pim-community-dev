import React, {KeyboardEvent, ReactNode, Ref, useCallback, useRef} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {Image} from '../../../components/Image/Image';
import {Checkbox} from '../../../components/Checkbox/Checkbox';
import {Link} from '../../../components/Link/Link';
import {Key, Override} from '../../../shared';
import {getBodyStyle} from 'typography';

const ItemLabel = styled.span`
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
  line-height: 34px;

  ${getBodyStyle({
    size: 'regular',
    color: 'grey',
    gradient: 120,
    weight: 'regular',
  })}
`;

const ItemContainer = styled.div<{tall: boolean} & AkeneoThemedProps>`
  background: ${getColor('white')};
  height: ${({tall}) => (tall ? '44px' : '34px')};
  padding: 0 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;

  a {
    ${getBodyStyle({
      size: 'regular',
      color: 'grey',
      gradient: 120,
      weight: 'regular',
    })}
  }
  &:focus ${ItemLabel} {
    ${getBodyStyle({
      size: 'regular',
      color: 'grey',
      gradient: 120,
      weight: 'regular',
    })}
  }
  &:hover {
    background: ${getColor('grey', 20)};
  }
  &:hover ${ItemLabel} {
    ${getBodyStyle({
      size: 'regular',
      color: 'brand',
      gradient: 140,
      weight: 'regular',
    })}
  }
  &:active ${ItemLabel} {
    ${getBodyStyle({
      size: 'regular',
      color: 'brand',
      gradient: 140,
      weight: 'bold',
    })}
    font-style: italic;
  }
  &:disabled ${ItemLabel} {
    ${getBodyStyle({
      size: 'regular',
      color: 'grey',
      gradient: 100,
      weight: 'regular',
    })}
  }
`;

type ItemProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * The content of the item.
     */
    children: ReactNode;
  }
>;

const Item = React.forwardRef<HTMLDivElement, ItemProps>(
  ({children, onKeyDown, ...rest}: ItemProps, forwardedRef: Ref<HTMLDivElement>): React.ReactElement => {
    let tall = false;
    const actionableRef = useRef<HTMLAnchorElement>(null);
    const handleClick = useCallback(() => {
      if (actionableRef.current !== null) {
        actionableRef.current.click();
      }
    }, []);
    const handleKeyDown = useCallback(
      (event: KeyboardEvent<HTMLDivElement>) => {
        if (Key.Enter === event.key || Key.Space === event.key) {
          event.preventDefault();
          handleClick();
          return;
        }

        onKeyDown && onKeyDown(event);
      },
      [onKeyDown, handleClick]
    );

    const decoratedChildren = React.Children.map(children, child => {
      if (typeof child === 'string') {
        return <ItemLabel title={child}>{child}</ItemLabel>;
      }

      // Change size of Image children
      if (React.isValidElement(child) && child.type === Image) {
        tall = true;

        return React.cloneElement(child, {
          width: 34,
          height: 34,
        });
      }

      // Transmit onclick and space and enter to Link children
      if (React.isValidElement(child) && child.type === Link) {
        return (
          <ItemLabel>
            {React.cloneElement(child, {
              ref: actionableRef,
              decorated: false,
            })}
          </ItemLabel>
        );
      }

      // Same for checkboxes
      if (React.isValidElement(child) && child.type === Checkbox) {
        return React.cloneElement(child, {
          ref: actionableRef,
        });
      }

      return child;
    });

    return (
      <ItemContainer
        tall={tall}
        tabIndex={actionableRef.current === null ? 0 : -1}
        onClick={handleClick}
        onKeyDown={handleKeyDown}
        {...rest}
        ref={forwardedRef}
      >
        {decoratedChildren}
      </ItemContainer>
    );
  }
);

export {Item, ItemLabel};
