import * as React from 'react';

const Fullscreen = ({
  color = '#67768A',
  title = 'Fullscreen icon',
  ...props
}: {color?: string; title?: string} & any) => (
  <svg viewBox="0 0 24 24" width="24" height="24" {...props}>
    <g fill="none" fillRule="evenodd" stroke={color} strokeLinecap="round" strokeLinejoin="round" strokeWidth="1">
      <path d="M9 21H3v-6m.5 5.5L9 15m6-12h6v6m-5.5-.5l5-5"></path>
      <path d="M9 21H3v-6m.5 5.5L9 15m6-12h6v6m-5.5-.5l5-5" transform="rotate(-90 12 12)"></path>
    </g>
    <title>{title}</title>
  </svg>
);

export default Fullscreen;
