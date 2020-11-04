import {useRouter} from '@akeneo-pim-community/legacy-bridge';
import {useAttributeContext} from '../contexts';

const useDeleteAttributeOption = () => {
  const router = useRouter();
  const attribute = useAttributeContext();

  return async (attributeOptionId: number) => {
    const response = await fetch(
      router.generate('pim_enrich_attributeoption_delete', {
        attributeId: attribute.attributeId,
        attributeOptionId: attributeOptionId,
      }),
      {
        method: 'DELETE',
        headers: [
          ['Content-type', 'application/json'],
          ['X-Requested-With', 'XMLHttpRequest'],
        ],
      }
    );
    switch (response.status) {
      case 400:
        const responseContent = await response.json();
        throw responseContent.message;
    }
  };
};

export {useDeleteAttributeOption};
