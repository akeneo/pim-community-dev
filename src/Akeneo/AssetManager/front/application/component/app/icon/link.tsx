import * as React from 'react';

const Link = ({
  color = '#67768A',
  title = 'Link icon',
  size = 24,
  ...props
}: {color?: string; title?: string; size?: number} & any) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    <g fill="none" fillRule="evenodd" stroke={color} strokeLinecap="round" strokeLinejoin="round" strokeWidth="1">
      <path
        d="M6.816 9.646c-.521.521-1.384.504-1.926-.04L.958 5.677C.416 5.133.398 4.27.92 3.749L3.75.92C4.271.398 5.133.416 5.676.96l3.93 3.93c.544.543.561 1.406.04 1.927"
        transform="translate(2 2)"
      ></path>
      <path
        d="M10.548 12.716c0-.737.622-1.334 1.39-1.334h5.56c.767 0 1.389.597 1.389 1.334v4.003c0 .737-.622 1.334-1.39 1.334h-5.56c-.767 0-1.39-.597-1.39-1.334"
        transform="translate(2 2) rotate(45 14.717 14.717)"
      ></path>
      <path strokeLinecap="round" d="M5.83 10h8.34" transform="translate(2 2) rotate(45 10 10)"></path>
    </g>
    <title>{title}</title>
  </svg>
);

export default Link;
