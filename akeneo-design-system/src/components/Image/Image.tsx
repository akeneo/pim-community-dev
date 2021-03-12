import React, {Ref} from 'react';
import styled, {css} from 'styled-components';
import {getColor, placeholderStyle} from '../../theme';
import {AkeneoThemedProps} from '../../theme';
import {Override} from '../../shared';

type Fit = 'cover' | 'contain';

const ImageContainer = styled.img<
  {
    fit: Fit;
    isStacked: boolean;
    isLoading: boolean;
  } & AkeneoThemedProps
>`
  background: ${getColor('white')};
  border: 1px solid ${getColor('grey', 80)};
  object-fit: ${({fit}) => fit};
  box-sizing: border-box;

  ${({isStacked}) =>
    isStacked &&
    css`
      box-shadow: 1px -1px 0 0 ${getColor('white')}, 2px -2px 0 0 ${getColor('grey', 80)},
        3px -3px 0 0 ${getColor('white')}, 4px -4px 0 0 ${getColor('grey', 80)};
    `}

  ${({isLoading}) => isLoading && placeholderStyle}
`;

type ImageProps = Override<
  React.ImgHTMLAttributes<HTMLImageElement>,
  {
    /**
     * Define the image source.
     */
    src: string | null;

    /**
     * Content of the alternative text.
     */
    alt: string;

    /**
     * The width of the image.
     */
    width?: number;

    /**
     * The height of the image.
     */
    height?: number;

    /**
     * Should the image cover all the container or be contained in it.
     */
    fit?: Fit;

    /**
     * Should the image appear as a stack of multiple images.
     */
    isStacked?: boolean;
  }
>;

/**
 * Image allow to embed an image in a page.
 */
const Image = React.forwardRef<HTMLImageElement, ImageProps>(
  ({fit = 'cover', isStacked = false, src, ...rest}: ImageProps, forwardedRef: Ref<HTMLImageElement>) => {
    return (
      <ImageContainer isLoading={null === src} src={src} ref={forwardedRef} fit={fit} isStacked={isStacked} {...rest} />
    );
  }
);

export {Image};
