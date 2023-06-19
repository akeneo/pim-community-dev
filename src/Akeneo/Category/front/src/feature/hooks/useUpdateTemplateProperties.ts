import {useRoute} from '@akeneo-pim-community/shared';
import {useMutation} from 'react-query';
import {BadRequestError, apiFetch} from '../tools/apiFetch';

export const useUpdateTemplateProperties = (templateUuid: string) => {
  const url = useRoute('pim_category_template_rest_update', {templateUuid});

  return useMutation<
    void,
    BadRequestError<{labels: {[localeCode: string]: string[]}}>,
    {labels: {[localeCode: string]: string}}
  >(data => apiFetch(url, {method: 'PATCH', body: JSON.stringify(data)}));
};
