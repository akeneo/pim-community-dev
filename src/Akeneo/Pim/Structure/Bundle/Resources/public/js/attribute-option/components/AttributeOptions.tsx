import React, {useCallback, useEffect, useState} from 'react';
import List from './List';
import Edit from './Edit';
import New from './New';
import {useDispatch, useSelector} from 'react-redux';
import {AttributeOptionsState} from '../store/store';
import {AttributeOption} from '../model';
import {useSaveAttributeOption} from '../hooks/useSaveAttributeOption';
import {useCreateAttributeOption} from '../hooks/useCreateAttributeOption';
import {updateAttributeOptionAction, createAttributeOptionAction} from '../reducers';
import {useAttributeContext} from '../contexts';

const AttributeOptions = () => {
    const attributeOptions = useSelector((state: AttributeOptionsState) => state.attributeOptions);
    const [selectedOption, setSelectedOption] = useState<AttributeOption | null>(null);
    const [isSaving, setIsSaving] = useState<boolean>(false);
    const [showNewOptionForm, setShowNewOptionForm] = useState<boolean>(false);
    const attributeOptionSaver = useSaveAttributeOption();
    const attributeOptionCreate = useCreateAttributeOption();
    const dispatchAction = useDispatch();
    const attribute = useAttributeContext();

    useEffect(() => {
        if (attributeOptions !== null && attributeOptions.length > 0 && selectedOption === null) {
            setSelectedOption(attributeOptions[0]);
        }
    }, [attributeOptions]);

    useEffect(() => {
        setSelectedOption(null);
    }, [attribute.attributeId]);

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

    return (
        <div className="AknAttributeOption">
            {(attributeOptions === null || isSaving) && <div className="AknLoadingMask"/>}

            <List
                selectAttributeOption={selectAttributeOption}
                selectedOptionId={selectedOption ? selectedOption.id : null}
                showNewOptionForm={setShowNewOptionForm}
            />

            {(selectedOption !== null && <Edit option={selectedOption} saveAttributeOption={saveAttributeOption}/>)}

            {(selectedOption === null && showNewOptionForm && <New createAttributeOption={createAttributeOption}/>)}
        </div>
    );
};

export default AttributeOptions;
