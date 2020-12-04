import {useRoute} from '@akeneo-pim-community/legacy-bridge';
import {useAttributeContext} from '../contexts';
import {AttributeOption} from '../model';

const useManualSortAttributeOptions = () => {
  const attribute = useAttributeContext();
  const route = useRoute('pim_enrich_attributeoption_update_sorting', {
    attributeId: attribute.attributeId.toString(),
  });

  return async (attributeOptions: AttributeOption[]) => {
    await fetch(route, {
      method: 'PUT',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(attributeOptions.map((option: AttributeOption) => option.id)),
    });
  };
};

export {useManualSortAttributeOptions};
