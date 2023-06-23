import {useMutation} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {apiFetch, BadRequestError} from '../tools/apiFetch';

type Form = {
  categoryTreeId: number;
  code: string;
  locale: string;
  label: string | null;
};

type MutationResult = {
  template_uuid: string;
}

export type CreateTemplateError = {
  templateCode: string[] | null;
  labels: {[locale: string]: string[]} | null;
};

export const useCreateTemplate = () => {
  const router = useRouter();
  return useMutation<MutationResult, BadRequestError<CreateTemplateError>, Form>(async (form: Form) => {
    const requestPayload = {
      code: form.code,
      labels: {[form.locale]: form.label},
    };
    return apiFetch<MutationResult, CreateTemplateError>(
      router.generate('pim_category_template_rest_create', {categoryTreeId: form.categoryTreeId}),
      {
        method: 'POST',
        body: JSON.stringify(requestPayload),
      }
    )
  });
};
