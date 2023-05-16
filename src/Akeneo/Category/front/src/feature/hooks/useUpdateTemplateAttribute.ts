import {useRoute} from '@akeneo-pim-community/shared';
import {useMutation} from 'react-query';
import {apiFetch} from '../tools/apiFetch';

export const useUpdateTemplateAttribute = (templateUuid: string, attributeUuid: string) => {
  const url = useRoute('pim_category_template_rest_update_attribute', {
    templateUuid: templateUuid,
    attributeUuid: attributeUuid,
  });

  const mutation = useMutation((isRichTextArea: boolean) =>
    apiFetch(url, {
      method: 'POST',
      body: JSON.stringify({isRichTextArea: isRichTextArea}),
    })
  );

  return async (isRichTextArea: boolean) => {
    await mutation.mutateAsync(isRichTextArea);
  };
};
