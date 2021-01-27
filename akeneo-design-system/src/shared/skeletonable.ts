import {FC, ForwardRefExoticComponent, RefAttributes} from 'react';

type WithSkeleton = {
  Skeleton: FC;
  __docgenInfo?: {description: string};
  displayName?: string;
};

type Skeletonable<Props> = FC<Props> & WithSkeleton;
type SkeletonableForwardRef<Element, Props> = ForwardRefExoticComponent<Props & RefAttributes<Element>> & WithSkeleton;

export type {Skeletonable, SkeletonableForwardRef};
