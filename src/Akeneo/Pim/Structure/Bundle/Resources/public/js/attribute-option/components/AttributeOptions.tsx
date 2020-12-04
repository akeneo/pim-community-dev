import React, {useCallback, useEffect, useState} from 'react';
import {useDispatch} from 'react-redux';

import List from './List';
import Edit from './Edit';
import New from './New';
import {AttributeOption} from '../model';
import {
  useCreateAttributeOption,
  useDeleteAttributeOption,
  useManualSortAttributeOptions,
  useSaveAttributeOption,
} from '../hooks/';
import {
  createAttributeOptionAction,
  deleteAttributeOptionAction,
  initializeAttributeOptionsAction,
  updateAttributeOptionAction,
} from '../reducers';
import {useAttributeContext} from '../contexts';
import {NotificationLevel, useNotify} from '@akeneo-pim-community/legacy-bridge';
import EmptyAttributeOptionsList from './EmptyAttributeOptionsList';
import useAttributeOptions from '../hooks/useAttributeOptions';

const AttributeOptions = () => {
  const attributeOptions = useAttributeOptions();
  const [selectedOption, setSelectedOption] = useState<AttributeOption | null>(null);
  const [isSaving, setIsSaving] = useState<boolean>(false);
  const [showNewOptionForm, setShowNewOptionForm] = useState<boolean>(false);
  const attributeOptionSaver = useSaveAttributeOption();
  const attributeOptionCreate = useCreateAttributeOption();
  const attributeOptionDelete = useDeleteAttributeOption();
  const attributeOptionManualSort = useManualSortAttributeOptions();
  const dispatchAction = useDispatch();
  const attributeContext = useAttributeContext();
  const notify = useNotify();

  useEffect(() => {
    if (
      attributeOptions !== null &&
      attributeOptions.length > 0 &&
      (selectedOption === null || !selectedOptionExists())
    ) {
      setSelectedOption(attributeOptions[0]);
    } else if (attributeOptions === null || attributeOptions.length === 0) {
      setSelectedOption(null);
    }
  }, [attributeOptions]);

  useEffect(() => {
    setSelectedOption(null);
  }, [attributeContext.attributeId]);

  const selectedOptionExists = () => {
    return (
      attributeOptions &&
      selectedOption &&
      attributeOptions.filter((option: AttributeOption) => option.id === selectedOption.id).length === 1
    );
  };

  const selectAttributeOption = useCallback(
    async (optionId: number | null) => {
      if (attributeOptions !== null) {
        const option = attributeOptions.find((option: AttributeOption) => option.id === optionId);
        if (option !== undefined) {
          setSelectedOption(option);
          setShowNewOptionForm(false);

          return;
        }
      }

      setShowNewOptionForm(false);
      setSelectedOption(null);
    },
    [attributeOptions, setSelectedOption, setShowNewOptionForm]
  );

  const saveAttributeOption = useCallback(
    async (attributeOption: AttributeOption) => {
      setIsSaving(true);
      try {
        await attributeOptionSaver(attributeOption);
        dispatchAction(updateAttributeOptionAction(attributeOption));
      } catch (error) {
        notify(NotificationLevel.ERROR, error);
      }
      setIsSaving(false);
    },
    [dispatchAction, setIsSaving]
  );

  const createAttributeOption = useCallback(
    async (optionCode: string) => {
      setIsSaving(true);
      try {
        const attributeOption = await attributeOptionCreate(optionCode);
        dispatchAction(createAttributeOptionAction(attributeOption));
        setShowNewOptionForm(false);
        setSelectedOption(attributeOption);
      } catch (error) {
        notify(NotificationLevel.ERROR, error);
      }
      setIsSaving(false);
    },
    [attributeOptionCreate, setIsSaving, dispatchAction, setShowNewOptionForm, setSelectedOption]
  );

  const deleteAttributeOption = useCallback(
    async (attributeOptionId: number) => {
      setIsSaving(true);
      try {
        await attributeOptionDelete(attributeOptionId);
        dispatchAction(deleteAttributeOptionAction(attributeOptionId));
        if (attributeOptions && selectedOption && selectedOption.id === attributeOptionId) {
          setSelectedOption(attributeOptions[0]);
        }
      } catch (error) {
        notify(NotificationLevel.ERROR, error);
      }
      setIsSaving(false);
    },
    [attributeOptionDelete, setIsSaving, dispatchAction, notify, setSelectedOption, attributeOptions, selectedOption]
  );

  const manuallySortAttributeOptions = useCallback(
    async (sortedAttributeOptions: AttributeOption[]) => {
      setIsSaving(true);
      await attributeOptionManualSort(sortedAttributeOptions);
      dispatchAction(initializeAttributeOptionsAction(sortedAttributeOptions));
      setIsSaving(false);
    },
    [setIsSaving, attributeOptionManualSort, dispatchAction]
  );

  return (
    <div className="AknAttributeOption">
      {(attributeOptions === null || isSaving) && <div className="AknLoadingMask" />}

      {attributeOptions !== null && attributeOptions.length === 0 && !showNewOptionForm && (
        <EmptyAttributeOptionsList showNewOptionForm={setShowNewOptionForm} />
      )}

      {(attributeOptions !== null && attributeOptions.length === 0 && !showNewOptionForm) || (
        <List
          selectAttributeOption={selectAttributeOption}
          selectedOptionId={selectedOption ? selectedOption.id : null}
          isNewOptionFormDisplayed={showNewOptionForm}
          showNewOptionForm={setShowNewOptionForm}
          deleteAttributeOption={deleteAttributeOption}
          manuallySortAttributeOptions={manuallySortAttributeOptions}
        />
      )}

      {selectedOption !== null && !showNewOptionForm && (
        <Edit option={selectedOption} saveAttributeOption={saveAttributeOption} />
      )}

      {selectedOption === null && showNewOptionForm && <New createAttributeOption={createAttributeOption} />}
    </div>
  );
};

export default AttributeOptions;
