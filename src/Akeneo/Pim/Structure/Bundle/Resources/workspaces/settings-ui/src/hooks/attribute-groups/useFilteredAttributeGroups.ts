import {useCallback, useEffect, useState} from 'react';
import {AttributeGroup} from '../../models';
import {useUserContext} from '@akeneo-pim-community/shared';

const useFilteredAttributeGroups = (groups: AttributeGroup[]) => {
  const [filteredGroups, setFilteredGroups] = useState<AttributeGroup[]>([]);
  const userContext = useUserContext();

  useEffect(() => {
    setFilteredGroups(groups);
  }, [groups]);

  const search = useCallback(
    (searchValue: string) => {
      setFilteredGroups(
        Object.values(groups).filter((group: AttributeGroup) =>
          (group.labels[userContext.get('catalogLocale')] ?? group.code)
            .toLowerCase()
            .includes(searchValue.toLowerCase().trim())
        )
      );
    },
    [groups]
  );

  return {
    filteredGroups,
    search,
  };
};

export {useFilteredAttributeGroups};
