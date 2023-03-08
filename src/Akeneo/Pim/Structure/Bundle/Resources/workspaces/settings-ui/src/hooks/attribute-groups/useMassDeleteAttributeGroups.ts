import {useBooleanState} from 'akeneo-design-system';
import {useRouter} from '@akeneo-pim-community/shared';
import {AttributeGroup} from '../../models';

const ATTRIBUTE_GROUP_MASS_DELETE_ROUTE = 'pim_structure_attributegroup_rest_mass_delete';

const useMassDeleteAttributeGroups = () => {
  const router = useRouter();
  const [isLoading, startLoading, stopLoading] = useBooleanState(false);

  const massDeleteAttributeGroups = async (
    attributeGroups: AttributeGroup[],
    replacementAttributeGroup: string | null
  ) => {
    startLoading();
    const response = await fetch(router.generate(ATTRIBUTE_GROUP_MASS_DELETE_ROUTE), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        codes: attributeGroups.map((attributeGroup: AttributeGroup) => attributeGroup.code),
        replacement_attribute_group: replacementAttributeGroup,
      }),
    });
    stopLoading();

    if (!response.ok) {
      throw new Error('Error while deleting attribute groups');
    }
  };

  return [isLoading, massDeleteAttributeGroups] as const;
};

export {useMassDeleteAttributeGroups};
