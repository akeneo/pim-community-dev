import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps} from 'akeneo-design-system';

type ImageProps = {
  src: string;
  alt: string;
};

const Image = (props: ImageProps & any): JSX.Element => {
  const imgElement = React.useRef<HTMLImageElement | null>(null);
  const [naturalWidth, setNaturalWidth] = React.useState<number>(0);

  return (
    <Container>
      <StyledImage
        {...props}
        ref={imgElement}
        maxWidth={338}
        naturalWidth={naturalWidth}
        onLoad={() => setNaturalWidth((imgElement.current as HTMLImageElement).naturalWidth)}
      />
    </Container>
  );
};

const Container = styled.div`
  width: 340px;
  border: 1px solid ${({theme}: AkeneoThemedProps) => theme.color.grey60};
`;

type StyledImageProps = {
  naturalWidth: number;
  maxWidth: number;
};

const StyledImage = styled.img`
  display: block;
  margin-left: auto;
  margin-right: auto;

  ${(props: ImageProps & StyledImageProps & AkeneoThemedProps) => {
    if (props.naturalWidth > props.maxWidth) {
      return `
        width: ${props.maxWidth}px;
      `;
    }

    return;
  }}
`;

export {Image};
