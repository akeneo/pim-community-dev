import {useEffect, useState} from 'react';
import {AttributeGroup, AttributeGroupCollection, fromAttributeGroupsCollection} from '../models';
import {fetchAllAttributeGroups} from '../infrastructure/fetchers';

const useAllAttributeGroups = (): AttributeGroup[] => {
    const [attributeGroups, setAttributeGroups] = useState<AttributeGroup[]>([]);

    useEffect(() => {
        fetchAllAttributeGroups().then((collection: AttributeGroupCollection) => {
            setAttributeGroups(fromAttributeGroupsCollection(collection));
        });
    }, []);

    return attributeGroups;
};

export {useAllAttributeGroups};
