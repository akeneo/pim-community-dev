import {useRoute} from '@akeneo-pim-community/shared';
import {useMutation} from 'react-query';
import {BadRequestError, apiFetch} from '../tools/apiFetch';

type ResponseError = {
  error: {
    property: string;
    message: string;
  };
};

type Body = {
  isRichTextArea?: boolean;
  labels?: {[locale: string]: string};
};

export const useUpdateTemplateAttribute = (templateUuid: string, attributeUuid: string) => {
  const url = useRoute('pim_category_template_rest_update_attribute', {
    templateUuid: templateUuid,
    attributeUuid: attributeUuid,
  });

  return useMutation<void, BadRequestError<ResponseError[]>, Body>(body =>
    apiFetch(url, {
      method: 'POST',
      body: JSON.stringify(body),
    })
  );
};
