import React from 'react';

const DownIcon = ({
  title = 'Down',
  color = '#67768A',
  size = 24,
  ...props
}: {title?: string; color?: string; size?: number} & any) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    <title>{title}</title>
    <path stroke={color} d="M2 7l10 10L22 7" fill="none" fillRule="evenodd" strokeLinecap="round" />
  </svg>
);

export {DownIcon};
