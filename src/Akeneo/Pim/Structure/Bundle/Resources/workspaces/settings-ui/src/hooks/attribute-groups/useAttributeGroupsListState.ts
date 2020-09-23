import {useCallback, useContext, useState} from "react";
import {AttributeGroup, AttributeGroupCollection, fromAttributeGroupsCollection} from "../../models";
import {useRedirectToAttributeGroup} from "./useRedirectToAttributeGroup";
import {fetchAllAttributeGroups} from "../../infrastructure/fetchers";
import {saveAttributeGroupsOrder} from "../../infrastructure/savers";
import {AttributeGroupsListContext, AttributeGroupsListState, MovePosition} from "../../components/shared/providers";

const useAttributeGroupsListState = (): AttributeGroupsListState => {
    const context = useContext(AttributeGroupsListContext);

    if (!context) {
        throw new Error("[Context]: You are trying to use 'useContext' outside Provider");
    }

    return context;
}

const useInitialAttributeGroupsListState = (): AttributeGroupsListState => {
    const [groups, setGroups] = useState<AttributeGroup[]>([]);

    const redirect = useRedirectToAttributeGroup();

    const refresh = useCallback((collection: AttributeGroupCollection) => {
        setGroups(fromAttributeGroupsCollection(collection));
    }, [setGroups]);

    const load = useCallback(async () => {
        return fetchAllAttributeGroups().then((collection: AttributeGroupCollection) => {
            refresh(collection);
        });
    }, [refresh]);

    const saveOrder = useCallback(async () => {
        let order: {[code: string]: number} = {};

        groups.forEach((attributeGroup) => {
            order[attributeGroup.code] = attributeGroup.sort_order;
        });

        console.log('saveOrder', order);

        const collection = await saveAttributeGroupsOrder(order);
        refresh(collection);
    }, [groups, refresh]);

    const move = useCallback((source: AttributeGroup, target: AttributeGroup, position: MovePosition) => {
        const newGroups: AttributeGroup[] = [];
        let order = 0;

        groups.forEach((group) => {
            if (group.code === source.code) {
                return;
            }

            if (position == MovePosition.Up && group.code === target.code) {
                newGroups.push({
                    ...source,
                    sort_order: order
                });
                order++;
            }

            newGroups.push({
                ...group,
                sort_order: order
            });

            order++;

            if (position == MovePosition.Down && group.code === target.code) {
                newGroups.push({
                    ...source,
                    sort_order: order
                });
                order++;
            }
        });

        setGroups(newGroups);
    }, [groups, setGroups]);

    return {
        groups,
        load,
        saveOrder,
        move,
        redirect
    };
};

export {useAttributeGroupsListState, useInitialAttributeGroupsListState, AttributeGroupsListState}