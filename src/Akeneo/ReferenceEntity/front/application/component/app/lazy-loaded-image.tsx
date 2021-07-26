import React, {useEffect, useState} from 'react';
import styled from 'styled-components';
import {loadInQueue} from 'akeneoreferenceentity/tools/image-loader';

const ImagePlaceholder = styled.div<{width: number; height: number}>`
  width: ${({width}) => width}px;
  height: ${({height}) => height}px;
`;

type LazyLoadedImageProps = {
  width: number;
  height: number;
  src: string;
} & React.ImgHTMLAttributes<HTMLImageElement>;

const LazyLoadedImage = ({width, height, src, ...rest}: LazyLoadedImageProps) => {
  const [imageSrc, setImageSrc] = useState<string | null>(null);

  useEffect(() => {
    const lazyLoadImage = async () => {
      await loadInQueue(src);
      setImageSrc(src);
    };

    lazyLoadImage();
  }, [src]);

  return null !== imageSrc ? (
    <img src={imageSrc} width={width} height={height} {...rest} />
  ) : (
    <ImagePlaceholder className="AknLoadingPlaceHolder" width={width} height={height} />
  );
};

export {LazyLoadedImage};
