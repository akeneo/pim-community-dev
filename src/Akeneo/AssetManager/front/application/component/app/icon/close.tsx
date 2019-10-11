import * as React from 'react';

const Close = (
  {color, title, size = 24, ...props}: {color: string; title?: string; size: number} & any = {color: '#67768A'}
) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    <g fillRule="nonzero" stroke={color} fill="none" strokeLinecap="round">
      <path d="M4 4l16 16M20 4L4 20" />
    </g>
    {title === undefined ? <title>Close</title> : <title>{title}</title>}
  </svg>
);

export default Close;
