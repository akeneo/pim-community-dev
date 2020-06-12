import React, {useEffect, useState} from 'react';
import {AttributeOption} from '../model';
import ToggleButton from './ToggleButton';
import ListItem, {DragItem} from './ListItem';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import NewOptionPlaceholder from './NewOptionPlaceholder';
import {useAttributeContext} from '../contexts';
import {useSelector} from 'react-redux';
import {AttributeOptionsState} from '../store/store';

interface ListProps {
    selectAttributeOption: (selectedOptionId: number | null) => void;
    isNewOptionFormDisplayed: boolean,
    showNewOptionForm: (isDisplayed: boolean) => void;
    selectedOptionId: number | null;
    deleteAttributeOption: (attributeOptionId: number) => void;
    manuallySortAttributeOptions: (attributeOptions: AttributeOption[]) => void;
}

const List = ({selectAttributeOption, selectedOptionId, isNewOptionFormDisplayed, showNewOptionForm, deleteAttributeOption, manuallySortAttributeOptions}: ListProps) => {
    const attributeOptions = useSelector((state: AttributeOptionsState) => state.attributeOptions);
    const translate = useTranslate();
    const attributeContext = useAttributeContext();
    const [showNewOptionPlaceholder, setShowNewOptionPlaceholder] = useState<boolean>(isNewOptionFormDisplayed);
    const [sortedAttributeOptions, setSortedAttributeOptions] = useState<AttributeOption[] | null>(attributeOptions);
    const [dragItem, setDragItem] = useState<DragItem | null>(null);

    useEffect(() => {
        if (selectedOptionId !== null) {
            setShowNewOptionPlaceholder(false);
        }
    }, [selectedOptionId]);

    useEffect(() => {
        if (attributeOptions !== null) {
            let sortedOptions = [...attributeOptions];
            if (attributeContext.autoSortOptions) {
                // /!\ sort() does not return another reference, it sorts directly on the original variable.
                sortedOptions.sort((option1: AttributeOption, option2: AttributeOption) => {
                    return option1.code.localeCompare(option2.code, undefined, {sensitivity: 'base'});
                });
            }
            setSortedAttributeOptions(sortedOptions);
        }
    }, [attributeOptions, attributeContext.autoSortOptions]);

    const onSelectItem = (optionId: number) => {
        setShowNewOptionPlaceholder(false);
        selectAttributeOption(optionId);
        showNewOptionForm(false);
    };

    const displayNewOptionPlaceholder = () => {
        setShowNewOptionPlaceholder(true);
        selectAttributeOption(null);
        showNewOptionForm(true);
    };

    const cancelNewOption = () => {
        showNewOptionForm(false);
        setShowNewOptionPlaceholder(false);
        if (attributeOptions !== null && attributeOptions.length > 0) {
            selectAttributeOption(attributeOptions[0].id);
        }
    };

    const moveAttributeOption = (sourceOptionCode: string, targetOptionCode: string) => {
        if (sortedAttributeOptions !== null && sourceOptionCode !== targetOptionCode) {
            const sourceIndex = sortedAttributeOptions.findIndex((attributeOption: AttributeOption) => attributeOption.code === sourceOptionCode);
            const targetIndex = sortedAttributeOptions.findIndex((attributeOption: AttributeOption) => attributeOption.code === targetOptionCode);
            const sourceOption = sortedAttributeOptions[sourceIndex];

            let newSortedAttributeOptions = [...sortedAttributeOptions];
            newSortedAttributeOptions.splice(sourceIndex, 1);
            newSortedAttributeOptions.splice(targetIndex, 0, sourceOption);

            setSortedAttributeOptions(newSortedAttributeOptions);
        }
    };

    const validateMoveAttributeOption = () => {
        if (sortedAttributeOptions !== null && JSON.stringify(sortedAttributeOptions) != JSON.stringify(attributeOptions)) {
            manuallySortAttributeOptions(sortedAttributeOptions);
        }
    };

    return (
        <div className="AknSubsection AknAttributeOption-list">
            <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
                <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_codes')}</span>
                <div className="AknButton AknButton--micro" onClick={() => displayNewOptionPlaceholder()} role="add-new-attribute-option-button">
                    {translate('pim_enrich.entity.product.module.attribute.add_option')}
                </div>
            </div>

            <div className="AknFieldContainer-header">
                {translate('pim_enrich.entity.attribute.property.auto_option_sorting')}
            </div>
            <ToggleButton />

            <div className="AknAttributeOption-list-optionsList" role="attribute-options-list">
                {sortedAttributeOptions !== null && sortedAttributeOptions.map((attributeOption: AttributeOption, index: number) => {
                    return (
                        <ListItem
                            key={attributeOption.code}
                            data={attributeOption}
                            selectAttributeOption={onSelectItem}
                            isSelected={selectedOptionId === attributeOption.id}
                            deleteAttributeOption={deleteAttributeOption}
                            moveAttributeOption={moveAttributeOption}
                            validateMoveAttributeOption={validateMoveAttributeOption}
                            dragItem={dragItem}
                            setDragItem={setDragItem}
                            index={index}
                        />
                    );
                })}

                {showNewOptionPlaceholder && (<NewOptionPlaceholder cancelNewOption={cancelNewOption}/>)}
            </div>
        </div>
    );
};

export default List;
