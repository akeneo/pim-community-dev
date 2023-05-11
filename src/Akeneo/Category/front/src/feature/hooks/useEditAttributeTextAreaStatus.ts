import {NotificationLevel, useNotify, useRoute} from '@akeneo-pim-community/shared';
import {useMutation} from 'react-query';
import {apiFetch} from '../tools/apiFetch';

type Props = {
  templateUuid: string;
  attributeUuid: string;
  isRichTextArea: boolean;
};

export const useEditAttributeTextAreaStatus = ({templateUuid, attributeUuid, isRichTextArea}: Props) => {
  const notify = useNotify();
  const url = useRoute('pim_category_template_rest_update_attribute', {
    templateUuid: templateUuid,
    attributeUuid: attributeUuid,
  });

  const mutation = useMutation(() =>
    apiFetch(url, {
      method: 'POST',
      body: JSON.stringify({isRichTextArea: isRichTextArea}),
    })
  );

  return async () => {
    await mutation.mutateAsync();
    notify(NotificationLevel.SUCCESS, 'RichTextArea Status updated');
  };
};
