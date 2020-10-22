import {SVGProps} from 'react';

type IconProps = {
  title?: string;
  size?: number;
  color?: string;
  className?: string;
} & SVGProps<SVGSVGElement>;

export type {IconProps};
