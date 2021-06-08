import React, {Ref, ReactNode, isValidElement, FC} from 'react';
import styled, {css} from 'styled-components';
import {IconProps} from '../../icons';
import {AkeneoThemedProps, getColor} from '../../theme';
import {Override} from '../../';

type Size = 'small' | 'big';

const TilesContainer = styled.div<{size: Size} & AkeneoThemedProps>`
  display: grid;
  ${({size}) =>
    size === 'small'
      ? css`
          gap: 20px;
          grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        `
      : css`
          gap: 30px;
          grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        `}
`;

const TileContainer = styled.div<{selected: boolean; size: Size; onClick?: () => void} & AkeneoThemedProps>`
  margin: 1px;
  ${({size}) =>
    size === 'small'
      ? css`
          height: 130px;
        `
      : css`
          height: 200px;
        `}
  transition: border-color 0.2s, color 0.2s, background 0.2s;
  ${({onClick}) =>
    onClick !== undefined &&
    css`
      cursor: pointer;
    `}
  text-align: center;
  ${({selected}) =>
    selected
      ? css`
          border: 2px solid ${getColor('blue', 100)};
          color: ${getColor('blue', 100)};
          margin: 0;
          background: ${getColor('blue', 10)};
        `
      : css`
          border: 1px solid ${getColor('grey', 80)};
        `}
  &:hover {
    border: 2px solid ${getColor('blue', 100)};
    color: ${getColor('blue', 100)};
    margin: 0;
    background: ${getColor('blue', 10)};
  }
  box-sizing: border-box;
`;

const IconContainer = styled.div<{size: Size} & AkeneoThemedProps>`
  box-sizing: content-box;
  ${({size}) =>
    size === 'small'
      ? css`
          padding: 25px 0 0 0;
          height: 54px;
        `
      : css`
          padding: 40px 0 0 0;
          height: 100px;
        `}
`;
const LabelContainer = styled.div`
  margin: 10px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.3;
`;

type TilesProps = {
  /**
   * Children are Tile components only
   */
  children?: ReactNode;

  /**
   * The size can be 'small' (by default) or 'big'
   */
  size?: Size;
};

type TileProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    icon: React.ReactElement<IconProps>;
    size?: Size;
    selected?: boolean;
    onClick?: () => void;
  }
>;

const Tile: FC<TileProps> = ({icon, selected = false, size = 'small', onClick, children, ...rest}) => {
  return (
    <TileContainer selected={selected} size={size} onClick={onClick} {...rest}>
      <IconContainer size={size}>{React.cloneElement(icon, {size: size === 'small' ? 54 : 100})}</IconContainer>
      <LabelContainer>{children}</LabelContainer>
    </TileContainer>
  );
};

/**
 * The Tiles component provides the user a list of choices, for example, an attribute type, a template, or an export
 * format.
 * It is a visual component made up of an icon and a label..
 */
const Tiles = React.forwardRef<HTMLDivElement, TilesProps>(
  ({size = 'small', children, ...rest}: TilesProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <TilesContainer size={size} ref={forwardedRef} {...rest}>
        {React.Children.map(children, child => {
          if (isValidElement<TileProps>(child) && child.type === Tile) {
            return React.cloneElement(child, {size});
          }
          throw new Error('A Tiles element can only have Tile children');
        })}
      </TilesContainer>
    );
  }
);

export {Tiles, Tile};
