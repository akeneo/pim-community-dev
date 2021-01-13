import {FC} from 'react';

type Skeletonable<Props> = FC<Props> & {
  Skeleton?: FC;
};

export type {Skeletonable};
