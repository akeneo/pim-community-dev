import React, {useEffect, useState} from 'react';
import List from './List';
import Edit from './Edit';
import {useDispatch, useSelector} from 'react-redux';
import {AttributeOptionsState} from '../store/store';
import {AttributeOption} from '../model';
import {useSaveAttributeOption} from '../hooks/useSaveAttributeOption';
import {updateAttributeOptionAction} from '../reducers';

const AttributeOptions = () => {
    const attributeOptions = useSelector((state: AttributeOptionsState) => state.attributeOptions);
    const [selectedOption, setSelectedOption] = useState<AttributeOption | null>(null);
    const [isSaving, setIsSaving] = useState<boolean>(false);
    const attributeOptionSaver = useSaveAttributeOption();
    const dispatchAction = useDispatch();

    useEffect(() => {
        if (attributeOptions !== null && attributeOptions.length > 0) {
            setSelectedOption(attributeOptions[0]);
        } else {
            setSelectedOption(null);
        }
    }, [attributeOptions]);

    useEffect(() => {
        setSelectedOption(null);

        return () => {
            setSelectedOption(null);
        };
    }, []);

    const onSelectAttributeOption = (optionId: number) => {
        if (attributeOptions !== null) {
            const option = attributeOptions.find((option: AttributeOption) => option.id === optionId);
            if (option !== undefined) {
                setSelectedOption(option);
            }
        }
    };

    const onSaveAttributeOption = async (attributeOption: AttributeOption) => {
        setIsSaving(true);
        await attributeOptionSaver(attributeOption);
        setIsSaving(false);
        dispatchAction(updateAttributeOptionAction(attributeOption));
    };

    return (
        <div className="AknAttributeOption">
            {(attributeOptions === null || isSaving) && <div className="AknLoadingMask"/>}

            <List onSelectAttributeOption={onSelectAttributeOption} selectedOptionId={selectedOption ? selectedOption.id : null}/>

            {(selectedOption !== null && <Edit option={selectedOption} onSave={onSaveAttributeOption}/>)}
        </div>
    );
};

export default AttributeOptions;
