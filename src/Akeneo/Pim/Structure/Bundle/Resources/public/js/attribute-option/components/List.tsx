import React, {FC, useCallback, useEffect, useState} from 'react';

import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AttributeOption} from '../model';
import {
    useAttributeContext,
    useAttributeOptionsContext,
    useAttributeOptionsListState,
    useSortedAttributeOptions
} from '../hooks';
import ToggleButton from './ToggleButton';
import ListItem, {DragItem} from './ListItem';
import NewOptionPlaceholder from './NewOptionPlaceholder';

const List: FC = () => {
    const translate = useTranslate();
    const {attributeId, autoSortOptions} = useAttributeContext();
    const {
        attributeOptions,
        selectedOption,
        isCreating,
        select,
        sort,
        deactivateCreation,
        activateCreation,
        initializeSelection
    } = useAttributeOptionsContext();
    const {extraData} = useAttributeOptionsListState(attributeOptions);
    const {
        sortedAttributeOptions,
        move,
        validate
    } = useSortedAttributeOptions(attributeOptions, autoSortOptions, sort);
    const [dragItem, setDragItem] = useState<DragItem | null>(null);

    const onSelectItem = useCallback(async (optionId: number) => {
        deactivateCreation();
        select(optionId);
    }, [select, deactivateCreation]);

    const displayNewOptionPlaceholder = useCallback(async () => {
        activateCreation();
    }, [activateCreation]);

    const cancelNewOption = useCallback(() => {
        deactivateCreation();
        if (attributeOptions !== null && attributeOptions.length > 0) {
            select(attributeOptions[0].id);
        }
    }, [deactivateCreation, attributeOptions, select]);

    useEffect(() => {
        initializeSelection(sortedAttributeOptions);
    }, [initializeSelection, sortedAttributeOptions]);

    useEffect(() => {
        select(null);
    }, [attributeId]);

    return (
        <div className="AknSubsection AknAttributeOption-list">
            <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
                <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_codes')}</span>
                <div
                    className="AknButton AknButton--micro"
                    role="add-new-attribute-option-button"
                    onClick={displayNewOptionPlaceholder}
                >
                    {translate('pim_enrich.entity.product.module.attribute.add_option')}
                </div>
            </div>

            <label className="AknFieldContainer-header" htmlFor="auto-sort-options">
                {translate('pim_enrich.entity.attribute.property.auto_option_sorting')}
            </label>

            <ToggleButton />

            <div className="AknAttributeOption-list-optionsList" role="attribute-options-list">
                {sortedAttributeOptions !== null && sortedAttributeOptions.map((attributeOption: AttributeOption, index: number) => {
                    return (
                        <ListItem
                            key={attributeOption.code}
                            data={attributeOption}
                            selectAttributeOption={onSelectItem}
                            isSelected={selectedOption === attributeOption}
                            moveAttributeOption={move}
                            validateMoveAttributeOption={validate}
                            dragItem={dragItem}
                            setDragItem={setDragItem}
                            index={index}
                        >
                            {extraData[attributeOption.code]}
                        </ListItem>
                    );
                })}

                {(isCreating()) && (<NewOptionPlaceholder cancelNewOption={cancelNewOption}/>)}
            </div>
        </div>
    );
};

export default List;
