import React, {isValidElement, KeyboardEvent, ReactNode, Ref, SyntheticEvent, useCallback, useRef} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {Image} from '../../../components/Image/Image';
import {Checkbox} from '../../../components/Checkbox/Checkbox';
import {Link} from '../../../components/Link/Link';
import {Key, Override} from '../../../shared';
import {LockIcon} from '../../../icons';

const ItemLabel = styled.span`
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
  line-height: 34px;
`;

const ItemContainer = styled.div<{tall: boolean; disabled: boolean} & AkeneoThemedProps>`
  background: ${getColor('white')};
  height: ${({tall}) => (tall ? 44 : 34)}px;
  line-height: ${({tall}) => (tall ? 44 : 34)}px;
  padding: 0 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  outline-style: none;
  cursor: pointer;
  white-space: nowrap;

  &:focus {
    box-shadow: inset 0 0 0 2px ${getColor('blue', 40)};
  }

  ${({disabled}) =>
    disabled
      ? css`
          cursor: not-allowed;
          color: ${getColor('grey', 100)};
        `
      : css`
          cursor: pointer;
          color: ${getColor('grey', 120)};
          a {
            color: ${getColor('grey', 120)};
          }

          &:hover a,
          &:hover {
            background: ${getColor('grey', 20)};
            color: ${getColor('brand', 140)};
          }
          &:active a,
          &:active {
            color: ${getColor('brand', 100)};
            font-weight: 700;
          }
        `}
`;

type ItemProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Whether or not the item is disabled.
     */
    disabled?: boolean;

    /**
     * The content of the item.
     */
    children: ReactNode;
  }
>;

const Item = React.forwardRef<HTMLDivElement, ItemProps>(
  (
    {children, onKeyDown, disabled = false, onClick, title, ...rest}: ItemProps,
    forwardedRef: Ref<HTMLDivElement>
  ): React.ReactElement => {
    let tall = false;
    const actionableRef = useRef<HTMLAnchorElement>(null);
    const handleClick = useCallback(
      (event: SyntheticEvent) => {
        if (disabled) return;

        if (null !== actionableRef.current && actionableRef.current !== event.target) {
          actionableRef.current.click();
        } else if (undefined !== onClick) {
          onClick(event as React.MouseEvent<HTMLDivElement, MouseEvent>);
        }
      },
      [disabled]
    );
    const handleKeyDown = useCallback(
      (event: KeyboardEvent<HTMLDivElement>) => {
        if (Key.Enter === event.key || Key.Space === event.key) {
          event.preventDefault();
          handleClick(event);
          return;
        }

        onKeyDown && onKeyDown(event);
      },
      [onKeyDown, handleClick]
    );

    const decoratedChildren = React.Children.map(children, child => {
      if (typeof child === 'string') {
        return (
          <>
            <ItemLabel title={title ?? child}>{child}</ItemLabel>
            {disabled && <LockIcon size={18} />}
          </>
        );
      }

      // Change size of Image children
      if (isValidElement(child) && child.type === Image) {
        tall = true;

        return React.cloneElement(child, {
          width: 34,
          height: 34,
        });
      }

      // Transmit onClick and space and enter to Link children
      if (isValidElement(child) && child.type === Link) {
        return (
          <>
            <ItemLabel>
              {React.cloneElement(child, {
                ref: actionableRef,
                decorated: false,
                disabled,
                tabIndex: -1,
              })}
            </ItemLabel>
            {disabled && <LockIcon size={18} />}
          </>
        );
      }

      // Same for Checkboxes
      if (isValidElement(child) && child.type === Checkbox) {
        return React.cloneElement(child, {
          ref: actionableRef,
          readOnly: disabled,
          tabIndex: -1,
        });
      }

      return child;
    });

    return (
      <ItemContainer
        tall={tall}
        tabIndex={null === actionableRef.current && !disabled ? 0 : -1}
        onClick={handleClick}
        onKeyDown={handleKeyDown}
        disabled={disabled}
        aria-disabled={disabled}
        {...rest}
        ref={forwardedRef}
      >
        {decoratedChildren}
      </ItemContainer>
    );
  }
);

export {Item, ItemLabel};
