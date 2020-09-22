import React from 'react';

const PartialCheckIcon = ({
  width = 27,
  height = 27,
  className = '',
}: {
  width: number;
  height: number;
  className?: string;
}) => (
  <svg width={width} height={height} viewBox="0 0 27 27">
    <path
      className={className}
      d="M2 12.5h20"
      stroke="currentColor"
      strokeWidth={2}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {PartialCheckIcon};
