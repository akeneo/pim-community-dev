import React, {createContext, FC} from 'react';
import {AttributeGroup} from "../../../models";
import {useInitialAttributeGroupsListState} from "../../../hooks/attribute-groups";

enum MovePosition {Up, Down};

type AttributeGroupsListState = {
    groups: AttributeGroup[];
    move: (source: AttributeGroup, target: AttributeGroup, position: MovePosition)  => void;
    saveOrder: () => Promise<void>;
    load: () => Promise<void>;
    redirect: (group: AttributeGroup) => void;
};

const AttributeGroupsListContext = createContext<AttributeGroupsListState>({
    groups: [],
    move: () => {},
    saveOrder: async () => {},
    load: async () => {},
    redirect: ()=> {},
});

const AttributeGroupsListProvider: FC = ({children}) => {
    const state = useInitialAttributeGroupsListState();
    return <AttributeGroupsListContext.Provider value={state}>{children}</AttributeGroupsListContext.Provider>;
};

export {AttributeGroupsListProvider, AttributeGroupsListState, AttributeGroupsListContext, MovePosition};