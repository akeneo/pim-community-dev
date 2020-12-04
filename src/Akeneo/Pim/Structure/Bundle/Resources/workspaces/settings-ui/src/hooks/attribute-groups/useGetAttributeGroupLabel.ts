import {useCallback} from 'react';
import {useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {AttributeGroup} from '../../models';

import {getLabel} from 'pimui/js/i18n';

type GetAttributeGroupLabelHandler = (group: AttributeGroup) => string;

const useGetAttributeGroupLabel = (): GetAttributeGroupLabelHandler => {
  const userContext = useUserContext();

  return useCallback(
    (group: AttributeGroup) => {
      return getLabel(group.labels, userContext.get('catalogLocale'), group.code);
    },
    [userContext]
  );
};

export {useGetAttributeGroupLabel};
