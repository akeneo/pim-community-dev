import React, {ReactNode, Ref} from 'react';

type TableBodyProps = {
  /**
   * Header rows
   */
  children?: ReactNode;
};

const TableBody = React.forwardRef<HTMLTableSectionElement, TableBodyProps>(
  ({children, ...rest}: TableBodyProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    return (
      <tbody ref={forwardedRef} {...rest}>
        {children}
      </tbody>
    );
  }
);

export {TableBody};
