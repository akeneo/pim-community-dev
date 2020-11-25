import React, {ReactNode, Ref} from 'react';
import {SelectableContext} from '../SelectableContext';

type TableHeaderProps = {
  /**
   * Header cells
   */
  children?: ReactNode;
};

const TableHeader = React.forwardRef<HTMLTableSectionElement, TableHeaderProps>(
  ({children, ...rest}: TableHeaderProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    const {isSelectable} = React.useContext(SelectableContext);

    return (
      <thead ref={forwardedRef}>
        <tr {...rest}>
          {/* Add new column for checkbox to be displayed properly in the tbody */}
          {isSelectable && <th />}
          {children}
        </tr>
      </thead>
    );
  }
);

export {TableHeader};
