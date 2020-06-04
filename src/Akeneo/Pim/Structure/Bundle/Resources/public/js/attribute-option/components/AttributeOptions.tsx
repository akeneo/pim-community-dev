import React, {useCallback, useEffect, useState} from 'react';
import List from './List';
import Edit from './Edit';
import New from './New';
import {useDispatch, useSelector} from 'react-redux';
import {AttributeOptionsState} from '../store/store';
import {AttributeOption} from '../model';
import {useSaveAttributeOption} from '../hooks/useSaveAttributeOption';
import {useCreateAttributeOption} from '../hooks/useCreateAttributeOption';
import {useDeleteAttributeOption} from '../hooks/useDeleteAttributeOption';
import {createAttributeOptionAction, deleteAttributeOptionAction, updateAttributeOptionAction} from '../reducers';
import {useAttributeContext} from '../contexts';
import {NotificationLevel, useNotify} from '@akeneo-pim-community/legacy-bridge';

const AttributeOptions = () => {
    const attributeOptions = useSelector((state: AttributeOptionsState) => state.attributeOptions);
    const [selectedOption, setSelectedOption] = useState<AttributeOption | null>(null);
    const [isSaving, setIsSaving] = useState<boolean>(false);
    const [showNewOptionForm, setShowNewOptionForm] = useState<boolean>(false);
    const attributeOptionSaver = useSaveAttributeOption();
    const attributeOptionCreate = useCreateAttributeOption();
    const attributeOptionDelete = useDeleteAttributeOption();
    const dispatchAction = useDispatch();
    const attribute = useAttributeContext();
    const notify = useNotify();

    useEffect(() => {
        if (attributeOptions !== null && attributeOptions.length > 0 && (selectedOption === null || !selectedOptionExists())) {
            setSelectedOption(attributeOptions[0]);
        } else if (attributeOptions === null || attributeOptions.length === 0) {
            setSelectedOption(null);
        }
    }, [attributeOptions]);

    useEffect(() => {
        setSelectedOption(null);
    }, [attribute.attributeId]);

    const selectedOptionExists = () => {
        return attributeOptions && selectedOption && attributeOptions.filter((option: AttributeOption) => option.id === selectedOption.id).length === 1;
    };

    const selectAttributeOption = useCallback(async (optionId: number | null) => {
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
    }, [attributeOptions, setSelectedOption, setShowNewOptionForm]);

    const saveAttributeOption = useCallback(async (attributeOption: AttributeOption) => {
        setIsSaving(true);
        await attributeOptionSaver(attributeOption);
        setIsSaving(false);
        dispatchAction(updateAttributeOptionAction(attributeOption));
    }, [dispatchAction, setIsSaving]);

    const createAttributeOption = useCallback(async (optionCode: string) => {
        setIsSaving(true);
        const attributeOption = await attributeOptionCreate(optionCode);
        setIsSaving(false);
        dispatchAction(createAttributeOptionAction(attributeOption));
        setShowNewOptionForm(false);
        setSelectedOption(attributeOption);
    }, [attributeOptionCreate, setIsSaving, dispatchAction, setShowNewOptionForm, setSelectedOption]);

    const deleteAttributeOption = useCallback(async (attributeOptionId: number) => {
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
    }, [attributeOptionDelete, setIsSaving, dispatchAction, notify, setSelectedOption, attributeOptions, selectedOption]);

    return (
        <div className="AknAttributeOption">
            {(attributeOptions === null || isSaving) && <div className="AknLoadingMask"/>}

            <List
                selectAttributeOption={selectAttributeOption}
                selectedOptionId={selectedOption ? selectedOption.id : null}
                showNewOptionForm={setShowNewOptionForm}
                deleteAttributeOption={deleteAttributeOption}
            />

            {(selectedOption !== null && <Edit option={selectedOption} saveAttributeOption={saveAttributeOption}/>)}

            {(selectedOption === null && showNewOptionForm && <New createAttributeOption={createAttributeOption}/>)}
        </div>
    );
};

export default AttributeOptions;
