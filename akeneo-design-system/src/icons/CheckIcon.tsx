import React from 'react';

const CheckIcon = ({width = 24, height = 24, className = ''}: {width: number; height: number; className?: string}) => (
  <svg width={width} height={height} viewBox="0 0 24 24">
    <path
      className={className}
      stroke="#FFFFFF"
      d="M2.5 12l7.5 6.5L21.5 6"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth={1}
      strokeMiterlimit={10}
    />
  </svg>
);

export {CheckIcon};
