import * as React from 'react';

const Download = (
  {color, title, ...props}: {color?: string; title?: string} & any = {color: '#67768A', title: 'Download icon'}
) => (
  <svg viewBox="0 0 24 24" width="24" height="24" {...props}>
    <g fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <g stroke={color}>
        <path className="base" d="M17.11 17H20v5H4v-5h3" />
        <path className="arrow" d="M12 2v16V2zM17 13l-5 5.5L7 13h0" />
      </g>
    </g>
  </svg>
);

export default Download;
