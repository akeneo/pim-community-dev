import * as React from 'react';

const Tick = ({className}: {className?: string}) => (
  <svg width={16} height={16}>
    <path
      className={undefined === className ? '' : className}
      fill="none"
      stroke="#FFFFFF"
      strokeWidth={1}
      strokeLinejoin="round"
      strokeMiterlimit={10}
      d="M1.7 8l4.1 4 8-8"
    />
  </svg>
);

export default Tick;
