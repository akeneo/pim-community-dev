import {useRouter} from '@akeneo-pim-community/shared';
import {useMutation} from 'react-query';
import {apiFetch} from '../tools/apiFetch';

type ReorderedAttributes = {
  templateUuid: string;
  uuids: string[];
};

export const useReorderAttributes = () => {
  const router = useRouter();
  return useMutation<void, void, ReorderedAttributes>(async (reorderedAttributes: ReorderedAttributes) => {
    return apiFetch<void, void>(
      router.generate('pim_category_template_rest_reorder_attributes', {
        templateUuid: reorderedAttributes.templateUuid,
      }),
      {
        method: 'POST',
        body: JSON.stringify(reorderedAttributes.uuids),
      }
    );
  });
};
