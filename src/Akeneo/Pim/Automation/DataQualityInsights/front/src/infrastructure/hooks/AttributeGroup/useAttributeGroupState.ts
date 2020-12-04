import {useEffect, useState} from 'react';
import fetchAttributeGroupStatus from '../../fetcher/AttributeGroup/fetchAttributeGroupStatus';
import saveAttributeGroupActivation from '../../saver/AttributeGroup/saveAttributeGroupActivation';

const useAttributeGroupState = (groupCode: string) => {
  const [isGroupActivated, setGroupActivation] = useState(true);

  useEffect(() => {
    (async () => {
      const response = await fetchAttributeGroupStatus(groupCode);
      setGroupActivation(response.activated);
    })();
  }, [groupCode]);

  const toggleGroupActivation = async () => {
    const isActivated = !isGroupActivated;
    setGroupActivation(isActivated);
    await saveAttributeGroupActivation(groupCode, isActivated);
  };

  return {
    isGroupActivated,
    toggleGroupActivation,
  };
};

export {useAttributeGroupState};
