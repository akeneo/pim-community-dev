import React, {createContext, FC} from 'react';
import {AttributeGroup} from '../../models';
import {useInitialAttributeGroupsIndexState} from '../../hooks/attribute-groups';
import {AfterMoveRowHandler, CompareRowDataHandler} from '../shared/providers';

type AttributeGroupsIndexState = {
  groups: AttributeGroup[];
  saveOrder: () => Promise<void>;
  load: () => Promise<void>;
  redirect: (group: AttributeGroup) => void;
  refresh: (refreshedGroups: AttributeGroup[]) => void;
  refreshOrder: AfterMoveRowHandler<AttributeGroup>;
  compare: CompareRowDataHandler<AttributeGroup>;
  isPending: boolean;
};

const AttributeGroupsIndexContext = createContext<AttributeGroupsIndexState>({
  groups: [],
  saveOrder: async () => {},
  load: async () => {},
  redirect: () => {},
  refresh: () => {},
  refreshOrder: () => {},
  compare: () => -1,
  isPending: true,
});

const AttributeGroupsIndexProvider: FC = ({children}) => {
  const state = useInitialAttributeGroupsIndexState();
  return <AttributeGroupsIndexContext.Provider value={state}>{children}</AttributeGroupsIndexContext.Provider>;
};

export {AttributeGroupsIndexProvider, AttributeGroupsIndexState, AttributeGroupsIndexContext};
