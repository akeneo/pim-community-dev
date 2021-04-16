import { RefObject, SVGProps } from 'react';
import { Override } from '../shared';
declare type IllustrationProps = Override<SVGProps<SVGSVGElement>, {
    title?: string;
    size?: number | string;
    className?: string;
    animateOnHover?: boolean;
    ref?: RefObject<SVGSVGElement>;
}>;
export type { IllustrationProps };
