import React, {Ref} from 'react';
import styled from 'styled-components';

const Container = styled.div`
  display: inline-block;
`;

const ImageContainer = styled.img<{fit: 'cover' | 'contain'}>`
  background: white;
  position: relative;
  height: 44px;
  width: 44px;
  border: 1px solid #ccd1d8;
  object-fit: ${({fit}) => fit};
`;

const StackedLayerContainer = styled.div`
  position: absolute;
  border: 1px solid #ccd1d8;
  background: white;
  height: 44px;
  width: 44px;
  transform: translate(4px, -4px);

  :after {
    content: '';
    position: absolute;
    border: 1px solid #ccd1d8;
    background: white;
    height: 44px;
    width: 44px;
    top: 1px;
    right: 1px;
  }
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
   * Should the image cover all the container or be contained in it.
   */
  fit?: 'cover' | 'contain';

  /**
   * Should the image is part of multiple images
   */
  isStacked?: boolean;
};

const Image = React.forwardRef<HTMLImageElement, ImageProps>(
  ({alt, src, fit = 'cover', isStacked = false, ...rest}: ImageProps, forwardedRef: Ref<HTMLImageElement>) => {
    return (
      <Container>
        {isStacked && <StackedLayerContainer />}
        <ImageContainer ref={forwardedRef} fit={fit} src={src} alt={alt} {...rest} />
      </Container>
    );
  }
);

export {Image};
