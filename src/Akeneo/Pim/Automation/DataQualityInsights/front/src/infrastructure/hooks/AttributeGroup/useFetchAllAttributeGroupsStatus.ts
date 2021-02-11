import {useCallback, useState} from 'react';
import {fetchAllAttributeGroupsDqiStatus} from '../../fetcher';

export type AttributeGroupsStatusCollection = {
  [code: string]: boolean;
};

const useFetchAllAttributeGroupsStatus = () => {
  const [status, setStatus] = useState<AttributeGroupsStatusCollection>({});
  const load = useCallback(async () => {
    const response = await fetchAllAttributeGroupsDqiStatus();
    setStatus(response);
  }, [setStatus]);

  return {
    status,
    load,
  };
};

export {useFetchAllAttributeGroupsStatus};
