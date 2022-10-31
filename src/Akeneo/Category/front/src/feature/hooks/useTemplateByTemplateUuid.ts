import {useQuery} from 'react-query';
import {Template} from '../models';
import {useRoute} from '@akeneo-pim-community/shared';
import {useCallback} from 'react';
import {ResponseStatus} from '../models/ResponseStatus';

const TEMPLATE_FETCH_STALE_TIME = 60 * 60 * 1000;

type Result = {
  status: ResponseStatus;
  data: Template | undefined;
  error: any;
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
