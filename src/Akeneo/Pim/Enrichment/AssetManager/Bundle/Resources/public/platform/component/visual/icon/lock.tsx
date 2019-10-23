import * as React from 'react';

export default ({size = 14}: {size?: number}) => {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width={size}>
      <path
        fill="none"
        fill-rule="evenodd"
        stroke="#67768A"
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M4 9h16a1 1 0 011 1v11a1 1 0 01-1 1H4a1 1 0 01-1-1V10a1 1 0 011-1zm4.5-.5V4.676C8.5 3.198 10.067 2 12 2s3.5 1.198 3.5 2.676V8.5M12 14v3"
      />
    </svg>
  );
};
