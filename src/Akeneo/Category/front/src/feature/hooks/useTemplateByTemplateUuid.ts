import {NotificationLevel, useNotify, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {useQuery} from 'react-query';
import {useHistory} from 'react-router';
import {Template} from '../models';
import {apiFetch} from '../tools/apiFetch';

export const useTemplateByTemplateUuid = (templateUuid: string | null) => {
  const router = useRouter();
  const translate = useTranslate();
  const notify = useNotify();
  const history = useHistory();

  const url = router.generate('pim_category_template_rest_get_by_template_uuid', {
    templateUuid,
  });

  return useQuery(['get-template', templateUuid], () => apiFetch<Template>(url, {}), {
    onError: () => {
      history.push('/');
      notify(NotificationLevel.ERROR, translate('akeneo.category.template.not_found'));
    },
  });
};
