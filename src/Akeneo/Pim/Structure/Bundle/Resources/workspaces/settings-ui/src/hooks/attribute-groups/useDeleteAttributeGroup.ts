import {useBooleanState} from 'akeneo-design-system';
import {useRouter} from '@akeneo-pim-community/shared';

const ATTRIBUTE_GROUP_DELETE_ROUTE = 'pim_enrich_attributegroup_rest_remove';

const useDeleteAttributeGroup = () => {
  const router = useRouter();
  const [isLoading, startLoading, stopLoading] = useBooleanState(false);

  const deleteAttributeGroup = async (attributeGroupCode: string, replacementAttributeGroupCode: string | null) => {
    startLoading();
    const response = await fetch(router.generate(ATTRIBUTE_GROUP_DELETE_ROUTE, {identifier: attributeGroupCode}), {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        replacement_attribute_group_code: replacementAttributeGroupCode,
      }),
    });
    stopLoading();

    if (!response.ok) {
      throw new Error('Error while deleting attribute group');
    }
  };

  return [isLoading, deleteAttributeGroup] as const;
};

export {useDeleteAttributeGroup};
