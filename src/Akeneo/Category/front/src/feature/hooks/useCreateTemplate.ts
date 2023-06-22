import {useMutation} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {apiFetch, BadRequestError} from '../tools/apiFetch';

type Form = {
  categoryTreeId: number;
  code: string;
  locale: string;
  label: string | null;
}

type CreateAttributeErrors = {[property: string]: string[]};

type ResponseError = {
  error: {
    property: string;
    message: string;
  };
};

type ApiResponseError = ResponseError[];

export const useCreateTemplate = () => {
  const router = useRouter();
  return useMutation<void, BadRequestError<CreateAttributeErrors>, Form>(async (form: Form) => {
    const requestPayload = {
      code: form.code,
      labels: {[form.locale]: form.label}
    };
    return apiFetch<void, ApiResponseError>(
      router.generate('pim_category_template_rest_create', {categoryTreeId: form.categoryTreeId}),
      {
        method: 'POST',
        body: JSON.stringify(requestPayload),
      }
    ).catch((error: BadRequestError<ApiResponseError>) => {

    })
  });

}
