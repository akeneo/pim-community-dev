import React, {SVGProps} from 'react';
import {theme} from '../theme';

export const UpdateIcon = (props: SVGProps<SVGSVGElement>) => (
    <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 19 22' {...props}>
        <path
            fill='none'
            stroke={theme.color.grey100}
            strokeLinecap='round'
            strokeLinejoin='round'
            d='M7.88621608,4 C4.96180094,5.60859385 3,8.57537883 3,11.9691789 C3,15.4068091 5.01280055,18.4063234 8,20 M10,19.5 L13,22 L13,17 L10,19.5 Z M15.1137839,4 C18.0381991,5.60859385 20,8.57537883 20,11.9691789 C20,15.4068091 17.9871995,18.4063234 15,20 M13,4.5 L10,7 L10,2 L13,4.5 Z'
            transform='translate(-2 -1)'
        />
    </svg>
);
