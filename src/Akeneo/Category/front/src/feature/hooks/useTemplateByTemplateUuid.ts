import {useQuery} from 'react-query';
import {Template} from '../models';
import {FetchStatus, NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {useMemo} from 'react';
import {useHistory} from 'react-router';

type Result = {
  status: FetchStatus;
  data: Template | undefined;
  error: any;
};

const getFetchingStatus = (status: 'idle' | 'loading' | 'error' | 'success'): FetchStatus => {
  if (status === 'loading') return 'fetching';
  if (status === 'success') return 'fetched';
  return status;
};

export const useTemplateByTemplateUuid = (uuid: string | null): Result => {
  const router = useRouter();
  const translate = useTranslate();
  const notify = useNotify();
  const history = useHistory();

  const url = useMemo(() => {
    if (uuid === null || uuid === undefined) {
      return null;
    }
    return router.generate('pim_category_template_rest_get_by_template_uuid', {
      templateUuid: uuid,
    });
  }, [router, uuid]);

  const fetchTemplate = async () => {
    if (url === null || uuid === null || uuid.length === 0) {
      return {};
    }

    return fetch(url).then(response => {
      if (!response.ok) {
        history.push('/');
        notify(NotificationLevel.ERROR, translate('akeneo.category.template.not_found'));
      }

      return response.json();
    });
  };

  const response = useQuery<Template, any>(['template', uuid], fetchTemplate, {enabled: uuid !== null && url !== null});

  return {
    ...response,
    status: getFetchingStatus(response.status),
  };
};
