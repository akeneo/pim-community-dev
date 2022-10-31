import {useQuery} from 'react-query';
import {Template} from '../models';
import {useRoute} from '@akeneo-pim-community/shared';
import {useCallback} from 'react';

const TEMPLATE_FETCH_STALE_TIME = 60 * 60 * 1000;

type Result = {
  status: 'idle' | 'loading' | 'success' | 'error';
  data: Template | undefined;
  error: any;
};

interface UseTemplateParameters {
  uuid: string;
  enabled?: boolean;
}

export const useTemplate = ({uuid, enabled = true}: UseTemplateParameters): Result => {
  const url = useRoute('pim_category_template_rest_get', {
    templateUuid: uuid,
  });

  const fetchTemplate = useCallback(async () => {
    if (uuid.length === 0) {
      return {};
    }

    return fetch(url).then(response => {
      return response.json();
    });
  }, [uuid, url]);

  const options = {
    enabled,
    staleTime: TEMPLATE_FETCH_STALE_TIME,
  };

  return useQuery<Template, any>(['template'], fetchTemplate, options);
};
