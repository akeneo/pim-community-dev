import * as React from 'react';

export const Copy = ({
  color = '#67768A',
  title = 'Copy icon',
  size = 24,
  ...props
}: {color?: string; title?: string; size?: number} & any) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    <path
      d="M17 7h5v15H7v-5M2 2h15v15H2V2z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
    <title>{title}</title>
  </svg>
);
