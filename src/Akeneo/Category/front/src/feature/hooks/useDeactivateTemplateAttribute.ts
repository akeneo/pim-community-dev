import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {useMutation} from 'react-query';
import {apiFetch} from '../tools/apiFetch';

export const useDeactivateTemplateAttribute = (templateUuid: string, attribute: {uuid: string; label: string}) => {
  const notify = useNotify();
  const translate = useTranslate();

  const url = useRoute('pim_category_template_rest_delete_attribute', {
    templateUuid: templateUuid,
    attributeUuid: attribute.uuid,
  });
  const mutation = useMutation(() => apiFetch(url, {method: 'DELETE'}));

  return async () => {
    await mutation.mutateAsync();
    notify(
      NotificationLevel.SUCCESS,
      translate('akeneo.category.template.delete_attribute.notification_success.title', {attribute: attribute.label})
    );
  };
};
