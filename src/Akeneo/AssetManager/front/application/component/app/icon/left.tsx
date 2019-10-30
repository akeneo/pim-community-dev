import * as React from 'react';

const Left = ({title = 'Left', color = '#67768A', ...props}: {title?: string; color?: string} & any) => (
  <svg width={24} height={24} {...props}>
    <title>{title}</title>
    <path stroke={color} d="M17 22L7 12 17 2" fill="none" fillRule="evenodd" strokeLinecap="round" />
  </svg>
);

export default Left;
