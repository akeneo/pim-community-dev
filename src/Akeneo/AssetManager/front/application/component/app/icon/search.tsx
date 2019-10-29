import * as React from 'react';

const Search = ({title, color = '#67768A', ...props}: {title?: string; color?: string} & any) => (
  <svg width={24} height={24} {...props}>
    {title === undefined ? <title>Search</title> : <title>{title}</title>}
    <g transform="translate(2 2)" fillRule="nonzero" stroke={color} fill="none">
      <path d="M12 12l7.5 7.5" strokeLinecap="round" />
      <circle cx={7} cy={7} r={7} />
    </g>
  </svg>
);

export default Search;
