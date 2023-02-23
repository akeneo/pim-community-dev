import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {useMutation} from 'react-query';
import {useHistory} from 'react-router';

export const useDeactivateTemplate = (template: {id: string; label: string}) => {
  const history = useHistory();
  const notify = useNotify();
  const translate = useTranslate();

  const url = useRoute('pim_enriched_category_rest_deactivate_template', {templateUuid: template.id});

  const mutation = useMutation(async () => {
    const response = await fetch(url, {method: 'DELETE'});

    if (!response.ok) {
      throw new Error();
    }
  });

  return async () => {
    await mutation.mutateAsync();

    notify(
      NotificationLevel.SUCCESS,
      translate('akeneo.category.template.deactivate.notification_success.title', {template: template.label}),
      translate('akeneo.category.template.deactivate.notification_success.message', {template: template.label})
    );
    history.push('/');
  };
};
