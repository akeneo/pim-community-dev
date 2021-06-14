import React from 'react';

type Props = {
  /**
   * The content of the panel when collapsed
   */
  children?: React.ReactNode;
};

const Collapse: React.FC<Props> = ({children}) => <>{children}</>;

export {Collapse};
