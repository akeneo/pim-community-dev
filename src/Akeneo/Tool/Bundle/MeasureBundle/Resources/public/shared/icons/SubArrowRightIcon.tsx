import React from 'react';

const SubArrowRightIcon = ({
  title = 'Sub arrow right',
  color = '#67768A',
  size = 24,
  ...props
}: {title?: string; color?: string; size?: number} & any) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    <title>{title}</title>
    <path stroke={color} d="M21 19H3V2m15 14l3 3-3 3" fill="none" fillRule="evenodd" strokeLinecap="round" />
  </svg>
);

export {SubArrowRightIcon};
