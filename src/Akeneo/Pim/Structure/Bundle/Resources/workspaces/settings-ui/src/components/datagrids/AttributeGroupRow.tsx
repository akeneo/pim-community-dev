import React, {FC} from 'react';
import styled from 'styled-components';
import {AttributeGroup} from '../../models';
import {useAttributeGroupLabel, useAttributeGroupsDataGridState} from '../../hooks';
import {DataGrid} from '../shared/datagrids';

type Props = {
  group: AttributeGroup;
  isEditable: boolean;
  index: number;
};

const Label = styled.span`
  width: 71px;
  height: 16px;
  color: ${({theme}) => theme.color.purple100};
  font-size: ${({theme}) => theme.fontSize.default};
  font-family: ${({theme}) => theme.font.default};
  font-weight: bold;
  font-style: italic;
`;

const AttributeGroupRow: FC<Props> = ({group, index, isEditable}) => {
  const {saveOrder, redirect} = useAttributeGroupsDataGridState();
  const label = useAttributeGroupLabel(group);

  return (
    <DataGrid.Row
      index={index}
      data={group}
      handleClick={() => {
        if (isEditable) {
          redirect(group);
        }
      }}
      handleDrop={() => {
        (async () => saveOrder())();
      }}
    >
      <DataGrid.Column>
        <Label>{label}</Label>
      </DataGrid.Column>
    </DataGrid.Row>
  );
};

export {AttributeGroupRow};
