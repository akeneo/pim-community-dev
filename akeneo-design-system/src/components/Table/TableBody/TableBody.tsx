import React, {ReactNode} from 'react';

type TableBodyProps = {
  /**
   * Header rows
   */
  children?: ReactNode;
};

const TableBody = ({children, ...rest}: TableBodyProps) => {
  return <tbody {...rest}>{children}</tbody>;
};

export {TableBody};
