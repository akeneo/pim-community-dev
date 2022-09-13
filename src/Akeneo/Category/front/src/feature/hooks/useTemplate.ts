import {useQuery} from 'react-query';
import {Template} from '../models';
import {useRoute} from '@akeneo-pim-community/shared';
import {useCallback} from 'react';

const TEMPLATE_FETCH_STALE_TIME = 60 * 60 * 1000;

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

  const fetchTemplate = useCallback(async () => {
    if (templateUuid.length === 0) {
      return {};
    }

    const response = await fetch(url);

    if (!response.ok) {
      throw new Error();
    }

    return await response.json();
  }, [templateUuid, url]);

  return useQuery<Template, ResultError, Template>(['template'], fetchTemplate, {staleTime: TEMPLATE_FETCH_STALE_TIME});
};
