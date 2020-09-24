import React, {createContext, FC} from 'react';
import {AttributeGroup} from "../../models";
import {useInitialAttributeGroupsListState} from "../../hooks/attribute-groups";
import {AfterMoveRowHandler, CompareRowDataHandler} from "../shared/providers";

type AttributeGroupsListState = {
    groups: AttributeGroup[];
    saveOrder: () => Promise<void>;
    load: () => Promise<void>;
    redirect: (group: AttributeGroup) => void;
    refresh: (refreshedGroups: AttributeGroup[]) => void;
    refreshOrder: AfterMoveRowHandler<AttributeGroup>;
    compare: CompareRowDataHandler<AttributeGroup>;
};

const AttributeGroupsListContext = createContext<AttributeGroupsListState>({
    groups: [],
    saveOrder: async () => {},
    load: async () => {},
    redirect: ()=> {},
    refresh: ()=> {},
    refreshOrder: ()=> {},
    compare: () => -1,
});

const AttributeGroupsListProvider: FC = ({children}) => {
    const state = useInitialAttributeGroupsListState();
    return <AttributeGroupsListContext.Provider value={state}>{children}</AttributeGroupsListContext.Provider>;
};

export {AttributeGroupsListProvider, AttributeGroupsListState, AttributeGroupsListContext};