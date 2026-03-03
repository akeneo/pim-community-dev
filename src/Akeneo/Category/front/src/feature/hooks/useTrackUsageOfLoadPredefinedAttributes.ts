import {useRoute} from '@akeneo-pim-community/shared';
import {apiFetch} from '../tools/apiFetch';
import {useMutation} from 'react-query';

type Action = 'load_predefined_attributes' | 'create_first_attribute';

export const useTrackUsageOfLoadPredefinedAttributes = (templateUuid: string) => {
  const url = useRoute('pim_category_template_track_usage_of_load_predefined_attributes', {templateUuid});

  const mutation = useMutation((action: Action) =>
    apiFetch(url, {
      method: 'POST',
      body: JSON.stringify({action}),
    })
  );

  return mutation.mutate;
};
