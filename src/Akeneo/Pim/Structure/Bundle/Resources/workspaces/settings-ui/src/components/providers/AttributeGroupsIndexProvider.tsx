import React, {createContext, FC} from 'react';
import {AttributeGroup} from '../../models';
import {useInitialAttributeGroupsIndexState} from '../../hooks/attribute-groups';
import {AfterMoveRowHandler} from '../shared/providers';

type AttributeGroupsIndexState = {
  attributeGroups: AttributeGroup[];
  saveOrder: (attributeGroups: AttributeGroup[]) => Promise<void>;
  load: () => Promise<void>;
  redirect: (group: AttributeGroup) => void;
  refresh: (refreshedGroups: AttributeGroup[]) => void;
  refreshOrder: AfterMoveRowHandler<AttributeGroup>;
  isPending: boolean;
};

const AttributeGroupsIndexContext = createContext<AttributeGroupsIndexState>({
  attributeGroups: [],
  saveOrder: async () => {},
  load: async () => {},
  redirect: () => {},
  refresh: () => {},
  refreshOrder: () => {},
  isPending: true,
});

const AttributeGroupsIndexProvider: FC = ({children}) => {
  const state = useInitialAttributeGroupsIndexState();
  return <AttributeGroupsIndexContext.Provider value={state}>{children}</AttributeGroupsIndexContext.Provider>;
};

export {AttributeGroupsIndexProvider, AttributeGroupsIndexState, AttributeGroupsIndexContext};
