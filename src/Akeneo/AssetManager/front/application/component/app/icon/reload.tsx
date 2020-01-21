import * as React from 'react';

const Reload = ({
  color = '#67768A',
  title,
  size = 24,
  ...props
}: {color: string; title?: string; size: number} & any) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    <g
      fill="none"
      fillRule="nonzero"
      stroke={color}
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeWidth="1"
      transform="translate(3 2)"
    >
      <path d="M4.886 2C1.962 3.609 0 6.575 0 9.97c0 3.437 2.013 6.436 5 8.03" />
      <path d="M16.886 2C13.962 3.609 12 6.575 12 9.97c0 3.437 2.013 6.436 5 8.03" transform="matrix(-1 0 0 1 29 0)" />
      <path d="M8.5 1L11 4 6 4z" transform="rotate(90 8.5 2.5)" />
      <path d="M8.5 16L11 19 6 19z" transform="matrix(0 1 1 0 -9 9)" />
    </g>
  </svg>
);

export default Reload;
