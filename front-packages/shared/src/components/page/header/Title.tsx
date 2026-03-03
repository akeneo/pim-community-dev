import React, {FC} from 'react';
import {SkeletonPlaceholder} from 'akeneo-design-system';
import styled from 'styled-components';

const Container = styled.div<{noTextTransform: boolean}>`
  color: ${({theme}) => theme.color.purple100};
  font-size: ${({theme}) => theme.fontSize.title};
  line-height: 34px;
  margin: 0;
  font-weight: normal;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex-grow: 1;

  &:first-letter {
    text-transform: ${({noTextTransform}) => (noTextTransform ? 'initial' : 'capitalize')};
  }
`;

type TitleProps = {
  showPlaceholder?: boolean;
  noTextTransform?: boolean;
};

const Title: FC<TitleProps> = ({children, showPlaceholder, noTextTransform}) => {
  return (
    <Container noTextTransform={noTextTransform ?? false}>
      {showPlaceholder ? <SkeletonPlaceholder>{children}</SkeletonPlaceholder> : <>{children}</>}
    </Container>
  );
};

export {Title};
export type {TitleProps};
