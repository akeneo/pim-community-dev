import {SVGProps} from 'react';

type IllustrationProps = {
  title?: string;
  size?: number | string;
  className?: string;
} & SVGProps<SVGSVGElement>;

export type {IllustrationProps};
