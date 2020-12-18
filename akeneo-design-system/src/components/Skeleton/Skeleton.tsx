import React, {ReactNode} from 'react';
import {SkeletonContext} from '../../context/Skeleton';
import {useSkeleton} from '../../hooks';
import {css, FlattenSimpleInterpolation} from 'styled-components';

const placeholderStyle = css`
  @keyframes loading-breath {
    0% {
      background-position: 0% 50%;
    }
    50% {
      background-position: 100% 50%;
    }
    100% {
      background-position: 0% 50%;
    }
  }

  animation: loading-breath 2s infinite;
  background: linear-gradient(270deg, #fdfdfd, #eee);
  background-size: 400% 400%;
  border: none;
  color: transparent;
`;

const applySkeletonStyle = (
  customStyle?: FlattenSimpleInterpolation
): (({skeleton}: SkeletonProps) => FlattenSimpleInterpolation | string) => ({skeleton}: SkeletonProps) =>
  skeleton
    ? css`
        ${placeholderStyle}
        ${customStyle}
      `
    : '';

type SkeletonProps = {
  /**
   * Define if children should be rendered as skeletons
   */
  skeleton: boolean;

  /**
   * Can be any element implementing SkeletonProps
   */
  children?: ReactNode;
};

/**
 * TODO.
 */
const Skeleton = ({skeleton, children}: SkeletonProps) => {
  const parentIsSkeleton = useSkeleton();

  return <SkeletonContext.Provider value={skeleton || parentIsSkeleton}>{children}</SkeletonContext.Provider>;
};

export {Skeleton, applySkeletonStyle};
export type {SkeletonProps};
