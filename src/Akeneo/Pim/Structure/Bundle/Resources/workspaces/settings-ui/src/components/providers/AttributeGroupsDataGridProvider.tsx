import React, {createContext, FC} from 'react';
import {AttributeGroup} from '../../models';
import {useInitialAttributeGroupsDataGridState} from '../../hooks/attribute-groups';
import {AfterMoveRowHandler, CompareRowDataHandler} from '../shared/providers';

type AttributeGroupsDataGridState = {
  groups: AttributeGroup[];
  saveOrder: () => Promise<void>;
  load: () => Promise<void>;
  redirect: (group: AttributeGroup) => void;
  refresh: (refreshedGroups: AttributeGroup[]) => void;
  refreshOrder: AfterMoveRowHandler<AttributeGroup>;
  compare: CompareRowDataHandler<AttributeGroup>;
};

const AttributeGroupsDataGridContext = createContext<AttributeGroupsDataGridState>({
  groups: [],
  saveOrder: async () => {},
  load: async () => {},
  redirect: () => {},
  refresh: () => {},
  refreshOrder: () => {},
  compare: () => -1,
});

const AttributeGroupsDataGridProvider: FC = ({children}) => {
  const state = useInitialAttributeGroupsDataGridState();
  return <AttributeGroupsDataGridContext.Provider value={state}>{children}</AttributeGroupsDataGridContext.Provider>;
};

export {AttributeGroupsDataGridProvider, AttributeGroupsDataGridState, AttributeGroupsDataGridContext};
