import {useCallback, useEffect, useState} from 'react';
import {useDispatch} from 'react-redux';

import useAttributeOptions from './useAttributeOptions';
import {useSaveAttributeOption} from './useSaveAttributeOption';
import {useCreateAttributeOption} from './useCreateAttributeOption';
import {useDeleteAttributeOption} from './useDeleteAttributeOption';
import {useManualSortAttributeOptions} from './useManualSortAttributeOptions';
import {NotificationLevel, useNotify} from '@akeneo-pim-community/legacy-bridge/src';
import {AttributeOption} from '../model';
import {
    createAttributeOptionAction,
    deleteAttributeOptionAction,
    initializeAttributeOptionsAction,
    updateAttributeOptionAction
} from '../reducers';

export type AttributeOptionsContextState = {
    attributeOptions: AttributeOption[]|null;
    selectedOption: AttributeOption|null;
    deactivateCreation: () => void;
    activateCreation: () => void;
    isEmpty: () => boolean;
    isEditing: () => boolean;
    isCreating: () => boolean;
    isLoading: () => boolean;
    save: (attributeOption: AttributeOption) => Promise<void>;
    remove: (attributeOptionId: number) => Promise<void>;
    create: (optionCode: string) => Promise<void>;
    select: (optionId: number|null) => void;
    sort: (sortedAttributeOptions: AttributeOption[]) => Promise<void>;
    initializeSelection: (attributeOptions: AttributeOption[]|null) => void;
};

export const initialAttributeOptionsContextState: AttributeOptionsContextState = {
    attributeOptions: null,
    selectedOption: null,
    deactivateCreation: () => {},
    activateCreation: () => {},
    isEmpty: () => false,
    isEditing: () => false,
    isCreating: () => false,
    isLoading: () => true,
    save: async () => {},
    remove: async () => {},
    create: async () => {},
    select: () => {},
    sort: async () => {},
    initializeSelection: () => {},
};

export const useAttributeOptionsContextState = (): AttributeOptionsContextState => {
    const attributeOptions = useAttributeOptions();
    const attributeOptionSaver = useSaveAttributeOption();
    const attributeOptionCreate = useCreateAttributeOption();
    const attributeOptionDelete = useDeleteAttributeOption();
    const attributeOptionManualSort = useManualSortAttributeOptions();
    const notify = useNotify();
    const dispatchAction = useDispatch();

    const [selectedOption, setSelectedOption] = useState<AttributeOption | null>(null);
    const [isSaving, setIsSaving] = useState<boolean>(false);
    const [showNewOptionForm, setShowNewOptionForm] = useState<boolean>(false);

    const save = useCallback(async (attributeOption: AttributeOption) => {
        setIsSaving(true);
        try {
            await attributeOptionSaver(attributeOption);
            dispatchAction(updateAttributeOptionAction(attributeOption));
        } catch (error) {
            notify(NotificationLevel.ERROR, error);
        }
        setIsSaving(false);
    }, [dispatchAction, setIsSaving]);

    const create = useCallback(async (optionCode: string) => {
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
    }, [attributeOptionCreate, setIsSaving, dispatchAction, setShowNewOptionForm, setSelectedOption]);

    const remove = useCallback(async (attributeOptionId: number) => {
        setIsSaving(true);
        try {
            await attributeOptionDelete(attributeOptionId);
            dispatchAction(deleteAttributeOptionAction(attributeOptionId));
        } catch (error) {
            notify(NotificationLevel.ERROR, error);
        }
        setIsSaving(false);
    }, [attributeOptionDelete, setIsSaving, dispatchAction, notify]);

    const select = useCallback((optionId: number | null) => {
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

    const sort = useCallback(async (sortedAttributeOptions: AttributeOption[]) => {
        setIsSaving(true);
        await attributeOptionManualSort(sortedAttributeOptions);
        dispatchAction(initializeAttributeOptionsAction(sortedAttributeOptions));
        setIsSaving(false);
    }, [setIsSaving, attributeOptionManualSort, dispatchAction]);

    const isEmpty = useCallback(() => {
        return (attributeOptions !== null && attributeOptions.length === 0);
    }, [attributeOptions]);

    const isEditing = useCallback(() => {
        return (selectedOption !== null && !showNewOptionForm);
    }, [selectedOption, showNewOptionForm]);

    const isCreating = useCallback(() => {
        return (selectedOption === null && showNewOptionForm);
    }, [selectedOption, showNewOptionForm]);

    const isLoading = () => {
        return (attributeOptions === null || isSaving);
    };

    const deactivateCreation = useCallback(() => {
        setShowNewOptionForm(false);
    }, [setShowNewOptionForm]);

    const activateCreation = useCallback(() => {
        setSelectedOption(null);
        setShowNewOptionForm(true);
    }, [setShowNewOptionForm, setSelectedOption]);

    const initializeSelection = useCallback((attributeOptions: AttributeOption[]|null) => {
        const selectedOptionExists = (optionsList: AttributeOption[], selection: AttributeOption) => {
            return (
                optionsList.filter((option: AttributeOption) => option.id === selection.id).length === 1
            );
        };

        if (isCreating()) {
            return;
        }

        if (isEditing() && attributeOptions && selectedOption && selectedOptionExists(attributeOptions, selectedOption)) {
            select(selectedOption.id);

            return;
        }

        if (
            attributeOptions !== null &&
            attributeOptions.length > 0 &&
            (selectedOption === null || !selectedOptionExists(attributeOptions, selectedOption))
        ) {
            select(attributeOptions[0].id);

            return;
        }

        if (attributeOptions === null || attributeOptions.length === 0) {
            select(null);

            return;
        }
    }, [select, selectedOption, isEditing, isCreating]);

    useEffect(() => {
        if (Array.isArray(attributeOptions) && selectedOption !== null) {
            const selection = attributeOptions.filter((option: AttributeOption) => option.id === selectedOption.id);
            if (selection.length === 0) {
                setSelectedOption(attributeOptions[0]);
            }
        }
    }, [attributeOptions, selectedOption, setSelectedOption]);

    return {
        attributeOptions,
        selectedOption,
        activateCreation,
        deactivateCreation,
        isEmpty,
        isEditing,
        isCreating,
        isLoading,
        save,
        remove,
        create,
        select,
        sort,
        initializeSelection,
    };
};
