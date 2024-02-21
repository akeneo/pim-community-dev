import {useMutation} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {apiFetch, BadRequestError} from '../tools/apiFetch';

type Form = {
  templateId: string;
  code: string;
  locale: string;
  label: string | null;
  type: string;
  isLocalizable: boolean;
  isScopable: boolean;
};

type CreateAttributeErrors = {[property: string]: string[]};

type ResponseError = {
  error: {
    property: string;
    message: string;
  };
};

type ApiResponseError = ResponseError[];

export const useCreateAttribute = () => {
  const router = useRouter();
  return useMutation<void, BadRequestError<CreateAttributeErrors>, Form>(async (form: Form) => {
    const requestPayload = {
      code: form.code,
      locale: form.locale,
      label: form.label,
      type: form.type,
      is_localizable: form.isLocalizable,
      is_scopable: form.isScopable,
    };
    return apiFetch<void, ApiResponseError>(
      router.generate('pim_category_template_rest_add_attribute', {templateUuid: form.templateId}),
      {
        method: 'POST',
        body: JSON.stringify(requestPayload),
      }
    ).catch((error: BadRequestError<ApiResponseError>) => {
      const exception = error.data.reduce((errors, currentData) => {
        if (!errors[currentData.error.property]) {
          errors[currentData.error.property] = [];
        }
        errors[currentData.error.property].push(currentData.error.message);
        return errors;
      }, {} as CreateAttributeErrors);

      throw new BadRequestError(exception);
    });
  });
};
