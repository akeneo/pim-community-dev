import {useQuery} from 'react-query';
import {Template} from '../models';
import {useRoute} from '@akeneo-pim-community/shared';

type ResultError = Error | null;
type Result = {
  isLoading: boolean;
  isError: boolean;
  data: Template | undefined;
  error: ResultError;
};

export const useTemplate = (templateUuid: string): Result => {
  const url = useRoute('pim_category_template_rest_get', {
    templateUuid: templateUuid,
  });
  return useQuery<Template, ResultError, Template>([], async () => {
    if (templateUuid.length === 0) {
      return {};
    }

    const response = await fetch(url);

    return await response.json();
  });
};
