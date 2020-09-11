import React from 'react';

const PartialCheckIcon = ({
  width = 24,
  height = 24,
  className = '',
}: {
  width: number;
  height: number;
  className?: string;
}) => (
  <svg width={width} height={height} viewBox="0 0 24 24">
    <path
      className={className}
      d="M2 12.5h20"
      stroke="#FFFFFF"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {PartialCheckIcon};
