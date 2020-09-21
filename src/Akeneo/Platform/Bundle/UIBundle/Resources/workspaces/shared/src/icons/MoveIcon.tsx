import React from 'react';
import {IconProps} from '../icons';
import {useAkeneoTheme} from '../hooks';

const MoveIcon = ({title = 'Icons / row', color, size = 24, ...props}: IconProps) => (
    <svg width={size} height={size} viewBox="0 0 24 24" shapeRendering="crispEdges" {...props}>
        <title>{title}</title>
        <g id="Icons-/-row"
            stroke="none"
            strokeWidth="1"
            fill="none"
            fillRule="evenodd"
            strokeLinecap="round"
            strokeLinejoin="round">
            <path
                d="M22,21.5 L2,21.5 L22,21.5 Z M22,11.5 L2,11.5 L22,11.5 Z M22,2 L2,2 L22,2 Z"
                id="Combined-Shape"
                stroke={color || useAkeneoTheme().color.grey100}
            />
        </g>
    </svg>
);

export {MoveIcon};
