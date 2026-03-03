import {useCallback} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {AttributeGroup} from '../../models';

const useRedirectToAttributeGroup = () => {
  const router = useRouter();

  return useCallback(
    (group: AttributeGroup) => {
      const url = router.generate('pim_enrich_attributegroup_edit', {
        identifier: group.code,
      });

      router.redirect(url);
    },
    [router]
  );
};

export {useRedirectToAttributeGroup};
