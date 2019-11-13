import * as React from 'react';

const Edit = ({color = '#67768A', title = 'Edit icon', ...props}: {color?: string; title?: string} & any) => (
  <svg viewBox="0 0 24 24" width="24" height="24" {...props}>
    <g fill="none" fillRule="evenodd" stroke={color} strokeLinecap="round" strokeLinejoin="round" strokeWidth="1">
      <path fillRule="nonzero" d="M3.5 4h3M3.5 12.5h1M11.984 12.5h2.126M3.5 16h10.605"></path>
      <path
        strokeLinejoin="round"
        d="M6.65845646 4.75015804L18.2395148 4.75015804 18.2395148 9.25015804 6.65845646 9.25015804 4.16433627 7.06541966z"
        transform="rotate(-42 11.202 7)"
      ></path>
      <path fillRule="nonzero" d="M7.67 9.149l1.897.146" transform="rotate(45 8.619 9.222)"></path>
      <path fillRule="nonzero" strokeLinejoin="round" d="M18 7.3L18 20 0 20 0 0 11 0"></path>
    </g>
    <title>{title}</title>
  </svg>
);

export default Edit;
