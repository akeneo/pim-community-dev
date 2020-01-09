import React, {SVGProps} from 'react';
import {theme} from '../theme';

export const ChevronDownIcon = ({
    color = theme.color.grey120,
    ...props
}: {color?: string} & SVGProps<SVGSVGElement>) => (
    <svg
        xmlns='http://www.w3.org/2000/svg'
        width='24'
        height='24'
        viewBox='0 0 24 24'
        preserveAspectRatio='xMidYMid meet'
        {...props}
    >
        <path fill={color} fillRule='evenodd' d='M2 7.6667L12 17l10-9.3333L21.2857 7 12 15.6667 2.7143 7' />
    </svg>
);
