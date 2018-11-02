import * as React from 'react';

const Download = (
  {color, title, ...props}: {color?: string; title?: string} & any = {color: '#67768A', title: 'Download icon'}
) => (
  <svg viewBox="0 0 24 24" {...props}>
    <g fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <g stroke={color}>
        <path className="base" d="M15 17h5v5H4v-5h5" />
        <path className="arrow" d="M12 18.5V2M7 7.5L12 2l5 5.5" />
      </g>
    </g>
  </svg>
);

export default Download;
