import {useQuery} from 'react-query';
// import {useUserContext} from '@akeneo-pim-community/shared'; //TODO
import {Template} from '../models';
import {useRoute} from "@akeneo-pim-community/shared";

type ResultError = Error | null;
type Result = {
  isLoading: boolean;
  isError: boolean;
  data: Template | undefined;
  error: ResultError;
};

// TODO later: remove hardcoded template uuid
export const useTemplate = (templateUuid: string = '02274dac-e99a-4e1d-8f9b-794d4c3ba330'): Result => {
  const url = useRoute('pim_category_template_rest_get', {
    templateUuid: templateUuid,
  });
  return useQuery<Template, ResultError, Template>(
    [],
    async () => {

      if (templateUuid.length === 0) {
        return {};
      }

      const response = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      return await response.json();
    }
  );
};
