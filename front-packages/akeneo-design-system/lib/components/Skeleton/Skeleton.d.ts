import { ReactNode } from 'react';
declare type SkeletonProps = {
    enabled?: boolean;
    children?: ReactNode;
};
declare const Skeleton: ({ enabled, children }: SkeletonProps) => JSX.Element;
export { Skeleton };
