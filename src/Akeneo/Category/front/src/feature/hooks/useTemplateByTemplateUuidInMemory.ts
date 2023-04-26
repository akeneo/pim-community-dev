import {useQuery} from 'react-query';
import {Template} from '../models';
import {useRoute} from '@akeneo-pim-community/shared';
import {useCallback} from 'react';
import {ResponseStatus} from '../models/ResponseStatus';

type ResultError = Error | null;
type Result = {
  status: ResponseStatus;
  data: Template | undefined;
  error: ResultError;
};

interface UseTemplateParameters {
  uuid: string;
  enabled?: boolean;
}

export const useTemplateByTemplateUuidInMemory = ({uuid, enabled = true}: UseTemplateParameters): Result => {
  const url = useRoute('pim_category_template_rest_get_by_template_uuid_in_memory', {
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

  return useQuery<Template, ResultError, Template>(['template'], fetchTemplate);
};
