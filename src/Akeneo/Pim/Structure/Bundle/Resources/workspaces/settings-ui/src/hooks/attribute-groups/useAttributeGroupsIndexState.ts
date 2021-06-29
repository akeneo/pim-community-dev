import {useCallback, useContext, useState} from 'react';
import {AttributeGroup, AttributeGroupCollection, toSortedAttributeGroupsArray} from '../../models';
import {useRedirectToAttributeGroup} from './useRedirectToAttributeGroup';
import {fetchAllAttributeGroups, fetchAllAttributeGroupsDqiStatus} from '../../infrastructure/fetchers';
import {saveAttributeGroupsOrder} from '../../infrastructure/savers';
import {AttributeGroupsIndexContext, AttributeGroupsIndexState} from '../../components/providers';

const FeatureFlags = require('pim/feature-flags');

const useAttributeGroupsIndexState = (): AttributeGroupsIndexState => {
  const context = useContext(AttributeGroupsIndexContext);

  if (!context) {
    throw new Error("[Context]: You are trying to use 'AttributeGroupsIndex' context outside Provider");
  }

  return context;
};

const useInitialAttributeGroupsIndexState = (): AttributeGroupsIndexState => {
  const [groups, setGroups] = useState<AttributeGroup[]>([]);
  const [isPending, setIsPending] = useState(true);

  const redirect = useRedirectToAttributeGroup();

  const refresh = useCallback(
    (list: AttributeGroup[]) => {
      setGroups(list);
    },
    [setGroups]
  );

  const load = useCallback(async () => {
    setIsPending(true);
    return fetchAllAttributeGroups().then(async (collection: AttributeGroupCollection) => {
      if (FeatureFlags.isEnabled('data_quality_insights')) {
        const groupDqiStatuses = await fetchAllAttributeGroupsDqiStatus();
        Object.entries(groupDqiStatuses).forEach(([groupCode, status]) => {
          collection[groupCode].isDqiActivated = status as boolean;
        });
      }
      setGroups(toSortedAttributeGroupsArray(collection));
      setIsPending(false);
    });
  }, [refresh]);

  const saveOrder = useCallback(async () => {
    let order: {[code: string]: number} = {};

    groups.forEach(attributeGroup => {
      order[attributeGroup.code] = attributeGroup.sort_order;
    });

    await saveAttributeGroupsOrder(order);
  }, [groups]);

  const refreshOrder = useCallback(
    (list: AttributeGroup[]) => {
      const reorderedGroups = list.map((item, index) => {
        return {
          ...item,
          sort_order: index,
        };
      });

      refresh(reorderedGroups);
    },
    [refresh]
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
