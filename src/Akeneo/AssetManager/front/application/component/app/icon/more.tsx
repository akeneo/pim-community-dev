import * as React from 'react';

const More = ({ title, color = '#67768A', ...props }: {title: string, color: string} & any) => (
  <svg width={24} height={24} {...props}>
    {title === undefined ? (
      <title>{'more'}</title>
    ) : (
      <title>{title}</title>
    )}
    <path
      d="M3.5 13.5a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm8.5 0a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm8.5 0a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"
      fill={color}
      fillRule="nonzero"
    />
  </svg>
)

export default More
