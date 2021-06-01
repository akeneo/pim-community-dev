import React, {Ref, ReactNode, isValidElement} from 'react';
import styled, {css} from 'styled-components';
import {IconProps} from '../../icons';
import {AkeneoThemedProps, getColor} from '../../theme';

type Size = 'small' | 'big';

const TilesContainer = styled.div<{size: Size} & AkeneoThemedProps>`
  display: flex;
  flex-wrap: wrap;
  gap: ${({size}) => (size === 'small' ? '20px' : '30px')};
`;

const TileContainer = styled.div<{selected: boolean; size: Size; onClick?: () => void} & AkeneoThemedProps>`
  ${({size}) =>
    size === 'small'
      ? css`
          width: 130px;
          height: 130px;
        `
      : css`
          width: 200px;
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
          margin: -1px;
          background: ${getColor('blue', 10)};
        `
      : css`
          border: 1px solid ${getColor('grey', 80)};
        `}
  &:hover {
    border: 2px solid ${getColor('blue', 100)};
    color: ${getColor('blue', 100)};
    margin: -1px;
    background: ${getColor('blue', 10)};
  }
`;

const IconContainer = styled.div<{size: Size} & AkeneoThemedProps>`
  ${({size}) =>
    size === 'small'
      ? css`
          padding: 26px 0 0 0;
        `
      : css`
          padding: 40px 0 0 0;
        `}
`;
const LabelContainer = styled.div`
  margin: 10px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.6;
`;

type TilesProps = {
  /**
   * TODO.
   */
  children?: ReactNode;

  size?: Size;
};

type TileProps = {
  label: string;
  icon: React.ReactElement<IconProps>;
  size?: Size;
  selected?: boolean;
  onClick?: () => void;
};

const Tile: React.FC<TileProps> = ({label, icon, selected = false, size, onClick, ...rest}) => {
  return (
    <TileContainer selected={selected} size={size} onClick={onClick} {...rest}>
      <IconContainer size={size}>{React.cloneElement(icon, {size: size === 'small' ? 54 : 100})}</IconContainer>
      <LabelContainer>{label}</LabelContainer>
    </TileContainer>
  );
};

/**
 * TODO.
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
