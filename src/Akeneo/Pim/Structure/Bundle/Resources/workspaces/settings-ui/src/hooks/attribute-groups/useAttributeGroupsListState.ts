import {useCallback, useContext, useState} from "react";
import {AttributeGroup, AttributeGroupCollection, fromAttributeGroupsCollection} from "../../models";
import {useRedirectToAttributeGroup} from "./useRedirectToAttributeGroup";
import {fetchAllAttributeGroups} from "../../infrastructure/fetchers";
import {saveAttributeGroupsOrder} from "../../infrastructure/savers";
import {AttributeGroupsListContext, AttributeGroupsListState} from "../../components/providers";

const useAttributeGroupsListState = (): AttributeGroupsListState => {
    const context = useContext(AttributeGroupsListContext);

    if (!context) {
        throw new Error("[Context]: You are trying to use 'AttributeGroupsList' context outside Provider");
    }

    return context;
}

const useInitialAttributeGroupsListState = (): AttributeGroupsListState => {
    const [groups, setGroups] = useState<AttributeGroup[]>([]);

    const redirect = useRedirectToAttributeGroup();

    const refresh = useCallback((list: AttributeGroup[]) => {
        setGroups(list);
    }, [setGroups]);

    const load = useCallback(async () => {
        return fetchAllAttributeGroups().then((collection: AttributeGroupCollection) => {
            setGroups(fromAttributeGroupsCollection(collection));
        });
    }, [refresh]);

    const saveOrder = useCallback(async () => {
        let order: {[code: string]: number} = {};

        groups.forEach((attributeGroup) => {
            order[attributeGroup.code] = attributeGroup.sort_order;
        });

        const collection = await saveAttributeGroupsOrder(order);
        setGroups(fromAttributeGroupsCollection(collection));
    }, [groups, refresh]);

    const refreshOrder = useCallback((list: AttributeGroup[]) => {
        const reorderedGroups = list.map((item, index) => {
            return {
                ...item,
                sort_order: index,
            }
        });

        refresh(reorderedGroups);
    }, [refresh]);

    const compare = (source: AttributeGroup, target: AttributeGroup) => {
        return source.code.localeCompare(target.code);
    }

    return {
        groups,
        load,
        saveOrder,
        redirect,
        refresh,
        refreshOrder,
        compare,
    };
};

export {useAttributeGroupsListState, useInitialAttributeGroupsListState, AttributeGroupsListState}