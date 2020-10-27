import {ForwardRefExoticComponent} from 'react';

type Component<T> = ForwardRefExoticComponent<T> & {
  __docgenInfo?: {description: string};
  displayName?: string;
};

export type {Component};
