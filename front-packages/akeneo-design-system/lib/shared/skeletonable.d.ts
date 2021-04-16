import { FC, ForwardRefRenderFunction } from 'react';
declare type WithSkeleton = {
    Skeleton: FC;
};
declare type Skeletonable<Props> = FC<Props> & WithSkeleton;
declare type SkeletonableForwardRef<Element, Props> = ForwardRefRenderFunction<Element, Props> & WithSkeleton;
export type { Skeletonable, SkeletonableForwardRef };
