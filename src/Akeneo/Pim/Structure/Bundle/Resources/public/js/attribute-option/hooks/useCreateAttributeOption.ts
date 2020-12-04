import {useRoute} from '@akeneo-pim-community/legacy-bridge';
import {useAttributeContext} from '../contexts';

const useCreateAttributeOption = () => {
  const attribute = useAttributeContext();
  const route = useRoute('pim_enrich_attributeoption_create', {
    attributeId: attribute.attributeId.toString(),
  });

  return async (attributeOptionCode: string) => {
    const response = await fetch(route, {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify({
        code: attributeOptionCode,
      }),
    });

    switch (response.status) {
      case 400:
        const responseContent = await response.json();
        throw responseContent.code;
    }

    return await response.json();
  };
};

export {useCreateAttributeOption};
