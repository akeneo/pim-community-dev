import {useQuery} from 'react-query';
import {Template} from '../models';
import {useRoute} from '@akeneo-pim-community/shared';
import {useCallback} from 'react';

const TEMPLATE_FETCH_STALE_TIME = 60 * 60 * 1000;

type ResultError = Error | null;
type Result = {
  status: 'idle' | 'loading' | 'success' | 'error';
  data: Template | undefined;
  error: ResultError;
};

interface UseTemplateParameters {
  uuid: string;
  enabled?: boolean;
}

export const useTemplateByTemplateUuid = ({uuid, enabled = true}: UseTemplateParameters): Result => {
  const url = useRoute('pim_category_template_rest_get_by_template_uuid', {
    templateUuid: uuid,
  });

  const fetchTemplate = useCallback(async () => {
    if (uuid.length === 0) {
      return {};
    }

    const response = await fetch(url);

    if (!response.ok) {
      throw new Error();
    }

    return await response.json();
  }, [uuid, url]);

  const options = {
    enabled,
    staleTime: TEMPLATE_FETCH_STALE_TIME,
  };

  return useQuery<Template, ResultError, Template>(['template'], fetchTemplate, options);
};
