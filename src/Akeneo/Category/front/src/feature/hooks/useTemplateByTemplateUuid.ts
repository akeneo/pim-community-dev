import {useQuery} from 'react-query';
import {Template} from '../models';
import {useRouter} from '@akeneo-pim-community/shared';
import {useCallback, useMemo} from 'react';
import {ResponseStatus} from '../models/ResponseStatus';

const TEMPLATE_FETCH_STALE_TIME = 60 * 60 * 1000;

type Result = {
  status: ResponseStatus;
  data: Template | undefined;
  error: any;
};

export const useTemplateByTemplateUuid = (uuid: string | null): Result => {
  const router = useRouter();
  const url = useMemo(() => {
    if (uuid === null) {
      return null;
    }
    return router.generate('pim_category_template_rest_get_by_template_uuid', {
      templateUuid: uuid,
    });
  }, [router, uuid]);

  const fetchTemplate = useCallback(async () => {
    if (url === null || uuid === null || uuid.length === 0) {
      return {};
    }

    return fetch(url).then(response => {
      return response.json();
    });
  }, [uuid, url]);

  const options = {
    enabled: uuid !== null && url !== null,
    staleTime: TEMPLATE_FETCH_STALE_TIME,
  };

  return useQuery<Template, any>(['template'], fetchTemplate, options);
};
