import {useCallback, useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';
import {saveAttributeGroupsOrder} from '../../infrastructure/savers';
import {AttributeGroup} from '../../models';

const ATTRIBUTE_GROUP_INDEX_ROUTE = 'pim_structure_attributegroup_rest_index';

const useAttributeGroups = () => {
  const [attributeGroups, setAttributeGroups] = useState<AttributeGroup[]>([]);
  const [isPending, setIsPending] = useState<boolean>(false);
  const route = useRoute(ATTRIBUTE_GROUP_INDEX_ROUTE);
  const [attributeGroupsIsReordered, setAttributeGroupsIsReordered] = useState<boolean>(false);

  const fetchAttributeGroups = useCallback(async () => {
    setIsPending(true);

    const response = await fetch(route);
    const attributeGroups = await response.json();

    setIsPending(false);
    setAttributeGroups(attributeGroups);
  }, [route]);

  const reorderAttributeGroups = useCallback((newIndices: number[]) => {
    setAttributeGroups(previousAttributeGroup => newIndices.map(newIndex => previousAttributeGroup[newIndex]));

    setAttributeGroupsIsReordered(true);
  }, []);

  const saveOrder = useCallback(async (attributeGroups: AttributeGroup[]) => {
    const order = attributeGroups.reduce<{[code: string]: number}>((accumulator, attributeGroup, index) => {
      accumulator[attributeGroup.code] = index;

      return accumulator;
    }, {});

    await saveAttributeGroupsOrder(order);
  }, []);

  useEffect(() => {
    if (!attributeGroupsIsReordered) return;

    void (async () => {
      await saveOrder(attributeGroups);
    })();
  }, [saveOrder, attributeGroups, attributeGroupsIsReordered]);

  useEffect(() => {
    void (async () => {
      await fetchAttributeGroups();
    })();
  }, [fetchAttributeGroups]);

  return [attributeGroups, reorderAttributeGroups, isPending] as const;
};

export {useAttributeGroups};
