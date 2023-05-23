import {useRoute} from '@akeneo-pim-community/shared';
import {useMutation} from 'react-query';
import {apiFetch} from '../tools/apiFetch';

type Body = {
  isRichTextArea?: boolean;
  labels?: {[locale: string]: string};
};

export const useUpdateTemplateAttribute = (templateUuid: string, attributeUuid: string) => {
  const url = useRoute('pim_category_template_rest_update_attribute', {
    templateUuid: templateUuid,
    attributeUuid: attributeUuid,
  });

  const mutation = useMutation((body: Body) =>
    apiFetch(url, {
      method: 'POST',
      body: JSON.stringify(body),
    })
  );
  return async (body: Body) => {
    await mutation.mutateAsync(body);
  };
};
