import React, {Ref} from 'react';
import styled, {css} from 'styled-components';

const ImageContainer = styled.img<{fit: 'cover' | 'contain'; height?: number; width?: number; isStacked: boolean}>`
  background: white;
  border: 1px solid #ccd1d8;
  object-fit: ${({fit}) => fit};
  transform: translate(4px, -4px);

  ${({isStacked}) =>
    isStacked &&
    css`
      box-shadow: 1px -1px 0 0 white, 2px -2px 0 0 #ccd1d8, 3px -3px 0 0 white, 4px -4px 0 0 #ccd1d8;
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
   * Should the image is part of multiple images
   */
  isStacked?: boolean;
};

const Image = React.forwardRef<HTMLImageElement, ImageProps>(
  ({fit = 'cover', isStacked = false, ...rest}: ImageProps, forwardedRef: Ref<HTMLImageElement>) => {
    return <ImageContainer ref={forwardedRef} fit={fit} isStacked={isStacked} {...rest} />;
  }
);

export {Image};
