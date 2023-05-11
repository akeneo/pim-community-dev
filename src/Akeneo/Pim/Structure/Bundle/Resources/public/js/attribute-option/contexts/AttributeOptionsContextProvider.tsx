import React, {createContext, FC, useCallback, useEffect, useState} from 'react';
import {AttributeOption, SpellcheckEvaluation} from '../model';
import {
  useCreateAttributeOption,
  useDeleteAttributeOption,
  useManualSortAttributeOptions,
  useSaveAttributeOption,
} from '../hooks';
import {useAttributeContext} from './AttributeContext';
import {useRoute} from '@akeneo-pim-community/shared';
import baseFetcher from '../fetchers/baseFetcher';
import {ATTRIBUTE_OPTION_UPDATED, ATTRIBUTE_OPTION_DELETED} from '../model/Events';

type AttributeOptionsState = {
  attributeOptions: AttributeOption[] | null;
  saveAttributeOption: (updatedAttributeOption: AttributeOption) => void;
  createAttributeOption: (optionCode: string) => Promise<AttributeOption>;
  deleteAttributeOption: (attributeOptionId: number) => void;
  reorderAttributeOptions: (sortedAttributeOptions: AttributeOption[]) => void;
  isSaving: boolean;
};

const AttributeOptionsContext = createContext<AttributeOptionsState>({
  attributeOptions: null,
  saveAttributeOption: () => {},
  createAttributeOption: optionCode => {
    return Promise.resolve({
      code: optionCode,
      id: 0,
      optionValues: {},
      toImprove: undefined,
    });
  },
  deleteAttributeOption: () => {},
  reorderAttributeOptions: () => {},
  isSaving: false,
});

type Props = {
  attributeOptionsQualityFetcher?: undefined | (() => Promise<SpellcheckEvaluation> | null);
};

const mergeAttributeOptionsEvaluation = (
  attributeOptions: AttributeOption[],
  evaluation: SpellcheckEvaluation | null
) => {
  if (!evaluation) {
    return attributeOptions;
  }

  Object.entries(evaluation.options).forEach(([optionCode, optionEvaluation]) => {
    const optionIndex = attributeOptions.findIndex(
      (attributeOption: AttributeOption) => attributeOption.code.toLowerCase() === optionCode.toLowerCase()
    );
    const attributeOptionToUpdate: AttributeOption | null = attributeOptions[optionIndex] ?? null;
    if (attributeOptionToUpdate) {
      attributeOptionToUpdate.toImprove = optionEvaluation.toImprove > 0;
      attributeOptions[optionIndex] = attributeOptionToUpdate;
    }
  });

  return attributeOptions;
};

const AttributeOptionsContextProvider: FC<Props> = ({children, attributeOptionsQualityFetcher}) => {
  const attribute = useAttributeContext();
  const [attributeOptions, setAttributeOptions] = useState<AttributeOption[] | null>(null);
  const attributeOptionSaver = useSaveAttributeOption();
  const attributeOptionCreate = useCreateAttributeOption();
  const attributeOptionDelete = useDeleteAttributeOption();
  const attributeOptionManualSort = useManualSortAttributeOptions();
  const [isSaving, setIsSaving] = useState<boolean>(false);
  const route = useRoute('pim_enrich_attributeoption_index', {attributeId: attribute.attributeId.toString()});

  const saveAttributeOption = useCallback(
    async (updatedAttributeOption: AttributeOption) => {
      if (!attributeOptions) {
        return;
      }
      setIsSaving(true);
      await attributeOptionSaver(updatedAttributeOption);

      let newAttributeOptions = attributeOptions.map((attributeOption: any) => {
        return attributeOption.id === updatedAttributeOption.id ? updatedAttributeOption : attributeOption;
      });
      if (attributeOptionsQualityFetcher) {
        const attributeOptionsEvaluation: SpellcheckEvaluation | null = await attributeOptionsQualityFetcher();
        newAttributeOptions = mergeAttributeOptionsEvaluation(newAttributeOptions, attributeOptionsEvaluation);
      }

      setAttributeOptions(newAttributeOptions);
      setIsSaving(false);
      window.dispatchEvent(new CustomEvent(ATTRIBUTE_OPTION_UPDATED));
    },
    [attributeOptions, attributeOptionSaver]
  );

  const createAttributeOption = useCallback(
    async (optionCode: string) => {
      setIsSaving(true);
      try {
        const attributeOption = await attributeOptionCreate(optionCode);
        if (attributeOptions === null) {
          setAttributeOptions([attributeOption]);
        } else {
          setAttributeOptions([...attributeOptions, attributeOption]);
        }

        return attributeOption;
      } catch (error) {
        throw error;
      } finally {
        setIsSaving(false);
      }
    },
    [attributeOptions, attributeOptionCreate]
  );

  const deleteAttributeOption = useCallback(
    async (attributeOptionId: number) => {
      if (!attributeOptions) {
        return;
      }
      setIsSaving(true);
      await attributeOptionDelete(attributeOptionId);
      const index = attributeOptions.findIndex(
        (attributeOption: AttributeOption) => attributeOption.id === attributeOptionId
      );
      let newAttributeOptions = [...attributeOptions];
      newAttributeOptions.splice(index, 1);
      setAttributeOptions(newAttributeOptions);
      setIsSaving(false);
      window.dispatchEvent(new CustomEvent(ATTRIBUTE_OPTION_DELETED));
    },
    [attributeOptions, attributeOptionDelete]
  );

  const reorderAttributeOptions = useCallback(async (sortedAttributeOptions: AttributeOption[]) => {
    setIsSaving(true);
    await attributeOptionManualSort(sortedAttributeOptions);
    setAttributeOptions(sortedAttributeOptions);
    setIsSaving(false);
  }, []);

  const handleRefreshEvaluation = useCallback(async () => {
    if (attributeOptionsQualityFetcher && attributeOptions !== null) {
      const attributeOptionsEvaluation: SpellcheckEvaluation | null = await attributeOptionsQualityFetcher();
      let newAttributeOptions = [...attributeOptions];
      newAttributeOptions = mergeAttributeOptionsEvaluation(newAttributeOptions, attributeOptionsEvaluation);
      setAttributeOptions(newAttributeOptions);
    }
  }, [attributeOptions, attributeOptionsQualityFetcher]);

  useEffect(() => {
    (async () => {
      if (attributeOptions === null) {
        let attributeOptions = await baseFetcher(route);

        if (attributeOptionsQualityFetcher) {
          const attributeOptionsEvaluation: SpellcheckEvaluation | null = await attributeOptionsQualityFetcher();
          attributeOptions = mergeAttributeOptionsEvaluation(attributeOptions, attributeOptionsEvaluation);
        }
        setAttributeOptions(attributeOptions);
      }
    })();
  }, []);

  useEffect(() => {
    window.addEventListener('refreshEvaluation', handleRefreshEvaluation);

    return () => {
      window.removeEventListener('refreshEvaluation', handleRefreshEvaluation);
    };
  }, [handleRefreshEvaluation]);

  return (
    <AttributeOptionsContext.Provider
      value={{
        attributeOptions,
        saveAttributeOption,
        createAttributeOption,
        deleteAttributeOption,
        reorderAttributeOptions,
        isSaving,
      }}
    >
      {children}
    </AttributeOptionsContext.Provider>
  );
};

export {AttributeOptionsContextProvider, AttributeOptionsContext};
