import {Template} from '../models';
import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {useHistory} from 'react-router';
import {apiFetch} from '../tools/apiFetch';
import {useQuery} from 'react-query';
import {useCallback} from 'react';

export const useTemplateByTemplateUuid = (uuid: string | null) => {
  const router = useRouter();
  const translate = useTranslate();
  const notify = useNotify();
  const history = useHistory();

  const url = router.generate('pim_category_template_rest_get_by_template_uuid', {
    templateUuid: uuid,
  });

  const fetchTemplate = useCallback(async () => {
    return await apiFetch<Template>(url, {method: 'GET'});
  }, [url]);

  const {data} = useQuery('get-template', fetchTemplate, {
    onError: () => {
      history.push('/');
      notify(NotificationLevel.ERROR, translate('akeneo.category.template.not_found'));
    },
  });
  return {
    status: 'fetched',
    data: data,
    error: null,
  };
};
