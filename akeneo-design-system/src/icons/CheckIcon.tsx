import React from 'react';

const CheckIcon = ({width = 18, height = 18, className = ''}: {width: number; height: number; className?: string}) => (
  <svg width={width} height={height} viewBox="0 0 18 18">
    <path
      className={className}
      stroke="currentColor"
      d="M1.7 8l4.1 4 8-8"
      fill="none"
      strokeLinejoin="round"
      strokeWidth={1}
      strokeMiterlimit={10}
    />
  </svg>
);

export {CheckIcon};
