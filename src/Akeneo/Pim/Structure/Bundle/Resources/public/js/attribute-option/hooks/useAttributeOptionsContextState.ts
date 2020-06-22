import {useCallback, useEffect, useState} from 'react';
import {useDispatch} from "react-redux";

import useAttributeOptions from "./useAttributeOptions";
import {useSaveAttributeOption} from "./useSaveAttributeOption";
import {useCreateAttributeOption} from "./useCreateAttributeOption";
import {useDeleteAttributeOption} from "./useDeleteAttributeOption";
import {NotificationLevel, useNotify} from "@akeneo-pim-community/legacy-bridge/src";
import {AttributeOption} from "../model";
import {
    createAttributeOptionAction,
    deleteAttributeOptionAction,
    initializeAttributeOptionsAction,
    updateAttributeOptionAction
} from "../reducers";
import {useManualSortAttributeOptions} from "./useManualSortAttributeOptions";

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
};

export const useAttributeOptionsContextState = (attributeId: number): AttributeOptionsContextState => {
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
            if (attributeOptions && selectedOption && selectedOption.id === attributeOptionId) {
                setSelectedOption(attributeOptions[0]);
            }
        } catch (error) {
            notify(NotificationLevel.ERROR, error);
        }
        setIsSaving(false);
    }, [attributeOptionDelete, setIsSaving, dispatchAction, notify, setSelectedOption, attributeOptions, selectedOption]);

    const selectedOptionExists = useCallback(() => {
        return (
            attributeOptions !== null &&
            selectedOption !== null &&
            attributeOptions.filter((option: AttributeOption) => option.id === selectedOption.id).length === 1
        );
    }, [attributeOptions, selectedOption]);

    const select = useCallback(async (optionId: number | null) => {
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
        return (attributeOptions !== null && attributeOptions.length === 0 && !showNewOptionForm);
    }, [attributeOptions, showNewOptionForm]);

    const isEditing = useCallback(() => {
        return (selectedOption !== null && !showNewOptionForm);
    }, [selectedOption, showNewOptionForm]);

    const isCreating = useCallback(() => {
        return (selectedOption === null && showNewOptionForm);
    }, [selectedOption, showNewOptionForm]);

    const isLoading = useCallback(() => {
        return (attributeOptions === null || isSaving);
    }, [attributeOptions, isSaving]);

    const deactivateCreation = useCallback(() => {
        setShowNewOptionForm(false);
    }, [setShowNewOptionForm]);

    const activateCreation = useCallback(() => {
        setShowNewOptionForm(true);
    }, [setShowNewOptionForm]);

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
    }, [attributeId]);

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
    };
};
