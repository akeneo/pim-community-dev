import {NotificationLevel, useNotify, useRoute} from '@akeneo-pim-community/shared';
import {useMutation} from 'react-query';
import {apiFetch} from '../tools/apiFetch';

type Translation = {
  locale: string;
  value: string;
};

export const useEditAttributeTranslations = (templateUuid: string, attributeUuid: string) => {
  // const notify = useNotify();
  // const url = useRoute('pim_category_template_rest_update_attribute', {
  //   templateUuid: templateUuid,
  //   attributeUuid: attributeUuid,
  // });
  //
  // const mutation = useMutation((translation: Translation) =>
  //   apiFetch(url, {
  //     method: 'POST',
  //     body: JSON.stringify({labels: translation}),
  //   })
  // );
  //
  // return (translation: Translation) => {
  //   mutation.mutate(translation, {
  //     onSuccess: () => {
  //       notify(NotificationLevel.SUCCESS, 'Attribute translation updated successfully');
  //     },
  //     onError: () => {},
  //   });
  // };
  return console.log('toto')
};
