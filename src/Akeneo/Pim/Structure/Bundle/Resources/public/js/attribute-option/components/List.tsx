import React, {FC, useCallback, useState} from 'react';

import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AttributeOption} from '../model';
import {useAttributeContext, useAttributeOptionsContext} from '../contexts';
import {useAttributeOptionsListState} from '../hooks/useAttributeOptionsListState';
import {useSortedAttributeOptions} from '../hooks/useSortedAttributeOptions';
import ToggleButton from './ToggleButton';
import ListItem, {DragItem} from './ListItem';
import NewOptionPlaceholder from './NewOptionPlaceholder';

const List: FC = () => {
    const translate = useTranslate();
    const {autoSortOptions} = useAttributeContext();
    const {
        attributeOptions,
        selectedOption,
        isCreating,
        select,
        sort,
        deactivateCreation,
        activateCreation
    } = useAttributeOptionsContext();
    const {extraData} = useAttributeOptionsListState(attributeOptions);
    const {
        sortedAttributeOptions,
        move,
        validate
    } = useSortedAttributeOptions(attributeOptions, autoSortOptions, sort);
    const [dragItem, setDragItem] = useState<DragItem | null>(null);

    const onSelectItem = useCallback(async (optionId: number) => {
        await select(optionId);
        deactivateCreation();
    }, [select, deactivateCreation]);

    const displayNewOptionPlaceholder = useCallback(() => {
        select(null);
        activateCreation();
    }, [select, activateCreation]);

    const cancelNewOption = useCallback(async () => {
        deactivateCreation();
        if (attributeOptions !== null && attributeOptions.length > 0) {
            await select(attributeOptions[0].id);
        }
    }, [deactivateCreation, attributeOptions, select]);

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
