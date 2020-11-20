import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {useSelectableContext} from '../SelectableContext';

type TableHeaderProps = {
  /**
   * Header cells
   */
  children?: ReactNode;
};

const HeaderRowContainer = styled.tr``;

const TableHeader = ({children, ...rest}: TableHeaderProps) => {
  const {isSelectable} = useSelectableContext();

  return (
    <thead>
      <HeaderRowContainer {...rest}>
        {isSelectable && <th />}
        {children}
      </HeaderRowContainer>
    </thead>
  );
};

export {TableHeader};
