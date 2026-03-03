import {useRoute} from '@akeneo-pim-community/shared';
import {useMutation} from 'react-query';
import {ApiError, apiFetch} from '../tools/apiFetch';

type Data = {labels: {[localeCode: string]: string}};

type Error = ApiError<{labels: {[localeCode: string]: string[]}}>;

export const useUpdateTemplateProperties = (templateUuid: string) => {
  const url = useRoute('pim_category_template_rest_update', {templateUuid});

  return useMutation<void, Error, Data>(data => apiFetch(url, {method: 'PATCH', body: JSON.stringify(data)}));
};
