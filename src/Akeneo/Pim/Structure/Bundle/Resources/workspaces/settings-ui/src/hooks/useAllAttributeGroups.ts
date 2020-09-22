import {useEffect, useState} from 'react';
import {AttributeGroup, AttributeGroupCollection, fromAttributeGroupsCollection} from '../models';
import {fetchAllAttributeGroups} from '../infrastructure/fetchers';

const useAllAttributeGroups = (): AttributeGroup[] => {
    const [groups, setGroups] = useState<AttributeGroup[]>([]);

    useEffect(() => {
        fetchAllAttributeGroups().then((collection: AttributeGroupCollection) => {
            setGroups(fromAttributeGroupsCollection(collection));
        });
    }, []);

    return groups;
};

export {useAllAttributeGroups};
