import React, {createContext, FC} from 'react';
import {AttributeGroup} from '../../models';
import {useInitialAttributeGroupsIndexState} from '../../hooks/attribute-groups';
import {AfterMoveRowHandler} from '../shared/providers';

type AttributeGroupsIndexState = {
  attributeGroups: AttributeGroup[];
  isSelected: boolean;
  saveOrder: (reorderedGroups: AttributeGroup[]) => Promise<void>;
  load: () => Promise<void>;
  redirect: (group: AttributeGroup) => void;
  refresh: (refreshedGroups: AttributeGroup[]) => void;
  refreshOrder: AfterMoveRowHandler<AttributeGroup>;
  isPending: boolean;
  selectAttributeGroup: (group: AttributeGroup) => void;
};

const AttributeGroupsIndexContext = createContext<AttributeGroupsIndexState>({
  attributeGroups: [],
  saveOrder: async () => {},
  load: async () => {},
  redirect: () => {},
  refresh: () => {},
  refreshOrder: () => {},
  selectAttributeGroup: () => {},
  isPending: true,
});

const AttributeGroupsIndexProvider: FC = ({children}) => {
  const state = useInitialAttributeGroupsIndexState();
  return <AttributeGroupsIndexContext.Provider value={state}>{children}</AttributeGroupsIndexContext.Provider>;
};

export {AttributeGroupsIndexProvider, AttributeGroupsIndexState, AttributeGroupsIndexContext};
