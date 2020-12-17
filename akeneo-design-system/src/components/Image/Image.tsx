import React, {Ref} from 'react';
import styled, {css} from 'styled-components';
import {getColor} from '../../theme';
import {AkeneoThemedProps} from '../../theme';

const ImageContainer = styled.img<
  {fit: 'cover' | 'contain'; height?: number; width?: number; isStacked: boolean} & AkeneoThemedProps
>`
  background: ${getColor('white')};
  border: 1px solid ${getColor('grey80')};
  object-fit: ${({fit}) => fit};

  ${({isStacked}) =>
    isStacked &&
    css`
      box-shadow: 1px -1px 0 0 ${getColor('white')}, 2px -2px 0 0 ${getColor('grey80')},
        3px -3px 0 0 ${getColor('white')}, 4px -4px 0 0 ${getColor('grey80')};
    `}

  ${({height}) =>
    height &&
    css`
      height: ${height}px;
    `}

  ${({width}) =>
    width &&
    css`
      height: ${width}px;
    `}
`;

type ImageProps = {
  /**
   * Define the image source
   */
  src: string;

  /**
   * Content of the alternative text
   */
  alt: string;

  /**
   * The width of the image
   */
  width?: number;

  /**
   * The height of the image
   */
  height?: number;

  /**
   * Should the image cover all the container or be contained in it.
   */
  fit?: 'cover' | 'contain';

  /**
   * Should the image appear as a stack of multiple images.
   */
  isStacked?: boolean;
} & React.ImgHTMLAttributes<HTMLImageElement>;

/**
 * Image allow to embed an image in a page
 */
const Image = React.forwardRef<HTMLImageElement, ImageProps>(
  ({fit = 'cover', isStacked = false, ...rest}: ImageProps, forwardedRef: Ref<HTMLImageElement>) => {
    return <ImageContainer ref={forwardedRef} fit={fit} isStacked={isStacked} {...rest} />;
  }
);

export {Image};
