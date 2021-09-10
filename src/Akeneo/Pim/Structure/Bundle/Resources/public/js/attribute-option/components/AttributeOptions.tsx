import React, {useCallback, useContext, useEffect, useState} from 'react';
import Edit from './Edit';
import New from './New';
import {AttributeOption} from '../model';
import {AttributeOptionsContext, useAttributeContext} from '../contexts';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/shared';
import EmptyAttributeOptionsList from './EmptyAttributeOptionsList';
import AttributeOptionTable from './AttributeOptionTable';

const AttributeOptions = () => {
  const {
    attributeOptions,
    saveAttributeOption,
    createAttributeOption,
    deleteAttributeOption,
    reorderAttributeOptions,
    isSaving,
  } = useContext(AttributeOptionsContext);
  const [selectedOption, setSelectedOption] = useState<AttributeOption | null>(null);
  const [showNewOptionForm, setShowNewOptionForm] = useState<boolean>(false);
  const attributeContext = useAttributeContext();
  const notify = useNotify();
  const translate = useTranslate();

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

  const saveAttributeOptionCallback = async (attributeOption: AttributeOption) => {
    try {
      await saveAttributeOption(attributeOption);
    } catch (error) {
      notify(NotificationLevel.ERROR, error);
    }
  };

  const createAttributeOptionCallback = async (optionCode: string) => {
    try {
      const attributeOption = await createAttributeOption(optionCode);
      setShowNewOptionForm(false);
      setSelectedOption(attributeOption);
    } catch (error) {
      notify(NotificationLevel.ERROR, error);
    }
  };

  const deleteAttributeOptionCallback = async (attributeOptionId: number) => {
    try {
      await deleteAttributeOption(attributeOptionId);
      if (attributeOptions && selectedOption && selectedOption.id === attributeOptionId) {
        setSelectedOption(attributeOptions[0]);
      }
    } catch (error) {
      notify(NotificationLevel.ERROR, translate(error));
    }
  };

  const manuallySortAttributeOptions = useCallback(
    async (sortedAttributeOptions: AttributeOption[]) => {
      await reorderAttributeOptions(sortedAttributeOptions);
    },
    [reorderAttributeOptions]
  );

  return (
    <div className="AknAttributeOption">
      {(attributeOptions === null || isSaving) && <div className="AknLoadingMask" />}

      {attributeOptions !== null && attributeOptions.length === 0 && !showNewOptionForm && (
        <EmptyAttributeOptionsList showNewOptionForm={setShowNewOptionForm} />
      )}

      {(attributeOptions !== null && attributeOptions.length === 0 && !showNewOptionForm) || (
        <AttributeOptionTable
          selectAttributeOption={selectAttributeOption}
          selectedOptionId={selectedOption ? selectedOption.id : null}
          isNewOptionFormDisplayed={showNewOptionForm}
          showNewOptionForm={setShowNewOptionForm}
          deleteAttributeOption={deleteAttributeOptionCallback}
          manuallySortAttributeOptions={manuallySortAttributeOptions}
        />
      )}

      {selectedOption !== null && !showNewOptionForm && (
        <Edit option={selectedOption} saveAttributeOption={saveAttributeOptionCallback} />
      )}

      {selectedOption === null && showNewOptionForm && <New createAttributeOption={createAttributeOptionCallback} />}
    </div>
  );
};

export default AttributeOptions;
