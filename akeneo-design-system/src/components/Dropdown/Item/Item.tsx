import React, {ReactNode, SyntheticEvent, Ref, useCallback} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {Checkbox, Image} from '../../../components';
import {CheckboxChecked} from 'components/Checkbox/Checkbox';
import {Override} from '../../../shared';

const ItemLabel = styled.span`
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
  color: ${getColor('grey', 120)};
  line-height: 34px;
`;

const ItemContainer = styled.div<AkeneoThemedProps>`
  background: ${getColor('white')};
  height: 34px;
  padding: 0 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;

  &:hover {
    background: ${getColor('grey', 20)};
  }
  &:hover ${ItemLabel} {
    color: ${getColor('brand', 140)};
  }

  &:active ${ItemLabel} {
    color: ${getColor('brand', 100)};
    font-style: italic;
  }

  &:disabled ${ItemLabel} {
    color: ${getColor('grey', 100)};
  }

  &:focus ${ItemLabel} {
    color: ${getColor('grey', 120)};
  }
`;

type ItemProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    children: ReactNode;
  }
>;

const Item = React.forwardRef<HTMLDivElement, ItemProps>(
  ({children, ...rest}: ItemProps, forwardedRef: Ref<HTMLDivElement>): React.ReactElement => {
    const decoratedChildren = React.Children.map(children, child => {
      if (typeof child === 'string') {
        return <ItemLabel>{child}</ItemLabel>;
      }

      return child;
    });

    return (
      <ItemContainer tabIndex={0} {...rest} ref={forwardedRef}>
        {decoratedChildren}
      </ItemContainer>
    );
  }
);

type SelectableItemProps = {
  children: ReactNode;
  selected?: boolean;
  onChange: (value: CheckboxChecked, event: SyntheticEvent) => void;
};

const SelectableItem = React.forwardRef<HTMLDivElement, SelectableItemProps>(
  (
    {children, selected = false, onChange, ...rest}: SelectableItemProps,
    forwardedRef: Ref<HTMLDivElement>
  ): React.ReactElement => {
    const handleChange = useCallback(
      event => {
        onChange(!selected, event);
        event.stopPropagation();
      },
      [selected]
    );

    return (
      <Item ref={forwardedRef} tabIndex={-1} onClick={handleChange} {...rest}>
        <Checkbox checked={selected} onChange={onChange}></Checkbox>
        {children}
      </Item>
    );
  }
);

const ImageItemContainer = styled(Item)`
  height: 44px;
`;

type ImageItemProps = {
  children: ReactNode;
};

const ImageItem = React.forwardRef<HTMLDivElement, ImageItemProps>(
  ({children, ...rest}: ImageItemProps, forwardedRef: Ref<HTMLDivElement>) => {
    const decoratedChildren = React.Children.map(children, child => {
      if (!React.isValidElement(child) || child.type !== Image) return child;

      return React.cloneElement(child, {
        width: 34,
        height: 34,
      });
    });

    return (
      <ImageItemContainer ref={forwardedRef} {...rest}>
        {decoratedChildren}
      </ImageItemContainer>
    );
  }
);

export {Item, SelectableItem, ImageItem, ItemLabel};
