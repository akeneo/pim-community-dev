import * as React from 'react';

const Right = ({title = 'Right', color = '#67768A', ...props}: {title?: string; color?: string} & any) => (
  <svg width={24} height={24} {...props}>
    <title>{title}</title>
    <path stroke={color} d="M7 2l10 10L7 22" fill="none" fillRule="evenodd" strokeLinecap="round" />
  </svg>
);

export default Right;
