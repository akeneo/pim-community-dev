import React from 'react';
import styled from 'styled-components';

const TreeLabelUnselected = styled.button`
  font-size: 15px;
  color: ${({theme}) => theme.color.grey140};
  border: none;
  padding: 0;
  background: none;
`;

const TreeLabelSelected = styled(TreeLabelUnselected)`
  font-weight: bold;
  color: ${({theme}) => theme.color.blue100};
`;

type Props = {
  onClick: () => void;
  selected: boolean;
};

const TreeLabel: React.FC<Props> = ({children, onClick, selected}) => {
  if (selected) {
    return (
      <TreeLabelSelected type='button' onClick={onClick}>
        {children}
      </TreeLabelSelected>
    );
  }
  return (
    <TreeLabelUnselected type='button' onClick={onClick}>
      {children}
    </TreeLabelUnselected>
  );
};

export {TreeLabel};
