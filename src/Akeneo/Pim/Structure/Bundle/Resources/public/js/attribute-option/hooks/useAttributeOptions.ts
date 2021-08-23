import {useCallback, useEffect, useState} from 'react';
import baseFetcher from '../fetchers/baseFetcher';
import {useAttributeContext} from '../contexts';
import {useRoute} from '@akeneo-pim-community/shared';
import {AttributeOption} from '../model';
import {useSaveAttributeOption} from './useSaveAttributeOption';

const useAttributeOptions = () => {
  const attribute = useAttributeContext();
  const [attributeOptions, setAttributeOptions] = useState<AttributeOption[] | null>(null);
  const attributeOptionSaver = useSaveAttributeOption();
  const route = useRoute('pim_enrich_attributeoption_index', {attributeId: attribute.attributeId.toString()});

  useEffect(() => {
    (async () => {
      if (attributeOptions === null) {
        const attributeOptions = await baseFetcher(route);
        setAttributeOptions(attributeOptions);
      }
    })();
  }, []);

  const saveAttributeOption = useCallback(async(updatedAttributeOption: AttributeOption) => {
    if (!attributeOptions) {
      return;
    }
    await attributeOptionSaver(updatedAttributeOption);
    const index = attributeOptions.findIndex(
      (attributeOption: AttributeOption) => attributeOption.id === updatedAttributeOption.id
    );

    let newAttributeOptions = [...attributeOptions];
    newAttributeOptions[index] = updatedAttributeOption;
    setAttributeOptions(newAttributeOptions);
  }, [attributeOptions]);

  return {
    attributeOptions,
    saveAttributeOption,
  };
};

export default useAttributeOptions;
