import {SkeletonContext} from '../context/Skeleton';
import {useContext} from 'react';

const useSkeleton = () => {
  return useContext(SkeletonContext);
};

export {useSkeleton};
