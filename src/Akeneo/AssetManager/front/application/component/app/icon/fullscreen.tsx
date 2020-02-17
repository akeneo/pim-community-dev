import * as React from 'react';

export const Fullscreen = ({
  color = '#67768A',
  title = 'Fullscreen icon',
  size = 24,
  ...props
}: {color?: string; title?: string; size?: number} & any) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    <g stroke={color} fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path d="M8.667 22H2v-6.667m.556 6.111l6.11-6.11M15.334 2H22v6.667m-6.111-.556l5.555-5.555M22 15.333V22h-6.667m6.111-.556l-6.11-6.11M2 8.666V2h6.667M8.11 8.111L2.556 2.556" />
    </g>
    <title>{title}</title>
  </svg>
);
