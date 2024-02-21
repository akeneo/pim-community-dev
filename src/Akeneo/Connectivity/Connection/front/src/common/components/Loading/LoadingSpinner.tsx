import React, {SVGProps} from 'react';

export const LoadingSpinner = (props: SVGProps<SVGSVGElement>) => (
    <svg
        className='lds-dash-ring'
        width='60px'
        height='60px'
        xmlns='http://www.w3.org/2000/svg'
        viewBox='0 0 100 100'
        style={{background: 'none)'}}
        preserveAspectRatio='xMidYMid'
        {...(props as any)}
    >
        <g transform='rotate(90 50 50)'>
            <animateTransform
                attributeName='transform'
                type='rotate'
                values='0 50 50;90 50 50'
                keyTimes='0;1'
                dur='0.7s'
                repeatCount='indefinite'
            ></animateTransform>
            <circle
                cx='50'
                cy='50'
                r='20'
                stroke='#9452ba'
                fill='none'
                strokeDasharray='15.707963267948966 125.66370614359172'
                strokeLinecap='round'
                strokeWidth='3'
                transform='rotate(0 50 50)'
            >
                <animate
                    attributeName='stroke'
                    values='#9452ba;#A975C8'
                    keyTimes='0;1'
                    dur='0.7s'
                    repeatCount='indefinite'
                ></animate>
            </circle>
            <circle
                cx='50'
                cy='50'
                r='20'
                stroke='#A975C8'
                fill='none'
                strokeDasharray='15.707963267948966 125.66370614359172'
                strokeLinecap='round'
                strokeWidth='3'
                transform='rotate(90 50 50)'
            >
                <animate
                    attributeName='stroke'
                    values='#A975C8;#BF97D6'
                    keyTimes='0;1'
                    dur='0.7s'
                    repeatCount='indefinite'
                ></animate>
            </circle>
            <circle
                cx='50'
                cy='50'
                r='20'
                stroke='#BF97D6'
                fill='none'
                strokeDasharray='15.707963267948966 125.66370614359172'
                strokeLinecap='round'
                strokeWidth='3'
                transform='rotate(180 50 50)'
            >
                <animate
                    attributeName='stroke'
                    values='#BF97D6;#D4BAE3'
                    keyTimes='0;1'
                    dur='0.7s'
                    repeatCount='indefinite'
                ></animate>
            </circle>
            <circle
                cx='50'
                cy='50'
                r='20'
                stroke='#D4BAE3'
                fill='none'
                strokeDasharray='15.707963267948966 125.66370614359172'
                strokeLinecap='round'
                strokeWidth='3'
                transform='rotate(270 50 50)'
            >
                <animate
                    attributeName='stroke'
                    values='#D4BAE3;#9452ba'
                    keyTimes='0;1'
                    dur='0.7s'
                    repeatCount='indefinite'
                ></animate>
            </circle>
        </g>
    </svg>
);
