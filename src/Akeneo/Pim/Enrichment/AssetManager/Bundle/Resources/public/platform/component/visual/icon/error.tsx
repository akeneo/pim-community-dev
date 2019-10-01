import * as React from 'react';

export default ({color}: {color: string}) => {
  return (<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24">
  <g fill="none" fillRule="evenodd">
    <path stroke={color} strokeLinecap="round" strokeLinejoin="round" d="M13.789 3.578l6.764 13.528A2 2 0 0 1 18.763 20H5.237a2 2 0 0 1-1.789-2.894l6.764-13.528a2 2 0 0 1 3.578 0zM12 6.5v7"/>
    <rect width="2" height="2" fill={color} rx="1" transform="translate(11 16)"/>
  </g>
</svg>)
}
