import {useCallback, useContext, useState} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {useRedirectToAttributeGroup} from './useRedirectToAttributeGroup';
import {saveAttributeGroupsOrder} from '../../infrastructure/savers';
import {AttributeGroupsIndexContext, AttributeGroupsIndexState} from '../../components';
import {AttributeGroup} from '../../models';

const useAttributeGroupsIndexState = (): AttributeGroupsIndexState => {
  const context = useContext(AttributeGroupsIndexContext);

  if (!context) {
    throw new Error("[Context]: You are trying to use 'AttributeGroupsIndex' context outside Provider");
  }

  return context;
};

const ATTRIBUTE_GROUP_INDEX_ROUTE = 'pim_structure_attributegroup_rest_index';

const useInitialAttributeGroupsIndexState = (): AttributeGroupsIndexState => {
  const [groups, setAttributeGroups] = useState<AttributeGroup[]>([]);
  const [isPending, setIsPending] = useState(true);
  const router = useRouter();

  const redirect = useRedirectToAttributeGroup();

  const refresh = useCallback(
    (list: AttributeGroup[]) => {
      setAttributeGroups(list);
    },
    [setAttributeGroups]
  );

  const load = useCallback(async () => {
    setIsPending(true);

    const route = router.generate(ATTRIBUTE_GROUP_INDEX_ROUTE);
    const response = await fetch(route);
    const groups = await response.json();

    setAttributeGroups(groups);
    setIsPending(false);
  }, [refresh, router]);

  const saveOrder = useCallback(async (reorderedGroups: AttributeGroup[]) => {
    const order: {[code: string]: number} = {};

    reorderedGroups.forEach(attributeGroup => {
      order[attributeGroup.code] = attributeGroup.sort_order;
    });

    await saveAttributeGroupsOrder(order);
  }, []);

  const refreshOrder = useCallback(
    async (list: AttributeGroup[]) => {
      const reorderedGroups = list.map((item, index) => {
        return {
          ...item,
          sort_order: index,
        };
      });
      setAttributeGroups(reorderedGroups);
      await saveOrder(reorderedGroups);
      await saveOrder(reorderedGroups);
    },
    [refresh, saveOrder]
  );

  const compare = (source: AttributeGroup, target: AttributeGroup) => {
    return source.code.localeCompare(target.code);
  };

  return {
    groups,
    load,
    saveOrder,
    redirect,
    refresh,
    refreshOrder,
    compare,
    isPending,
  };
};

export {useAttributeGroupsIndexState, useInitialAttributeGroupsIndexState, AttributeGroupsIndexState};
