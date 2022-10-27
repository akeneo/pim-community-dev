import React from 'react';
import {Styled} from './Styled';

const ListSkeleton: React.FC = () => (
  <>
    <Styled.SkeletonContainer>
      <Styled.Skeleton />
      <Styled.Skeleton />
      <Styled.Skeleton />
    </Styled.SkeletonContainer>
  </>
);

export {ListSkeleton};
