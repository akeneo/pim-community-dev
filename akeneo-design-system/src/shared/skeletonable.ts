import {FC, ForwardRefRenderFunction} from 'react';

type WithSkeleton = {
  Skeleton: FC;
};

type Skeletonable<Props> = FC<Props> & WithSkeleton;
type SkeletonableForwardRef<Element, Props> = ForwardRefRenderFunction<Element, Props> & WithSkeleton;

export type {Skeletonable, SkeletonableForwardRef};
