import React, {SVGProps} from 'react';
import {theme} from '../theme';

export const DuplicateIcon = (props: SVGProps<SVGSVGElement>) => (
    <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 22 22' {...props}>
        <path
            fill='none'
            stroke={theme.color.grey100}
            strokeLinecap='round'
            strokeLinejoin='round'
            d='M15,5 L20,5 L20,20 L5,20 L5,15 M0,0 L15,0 L15,15 L0,15 L0,0 Z'
            transform='translate(1 1)'
        />
    </svg>
);
