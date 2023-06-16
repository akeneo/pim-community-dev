import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {useQuery} from 'react-query';
import {useHistory} from 'react-router';
import {Template} from '../models';
import {apiFetch} from '../tools/apiFetch';

export const useTemplateByTemplateUuid = (uuid: string | null) => {
  const router = useRouter();
  const translate = useTranslate();
  const notify = useNotify();
  const history = useHistory();

  const url = router.generate('pim_category_template_rest_get_by_template_uuid', {
    templateUuid: uuid,
  });

  return useQuery(['get-template', uuid], () => apiFetch<Template>(url, {}), {
    enabled: null !== uuid,
    onError: () => {
      history.push('/');
      notify(NotificationLevel.ERROR, translate('akeneo.category.template.not_found'));
    },
  });
};
