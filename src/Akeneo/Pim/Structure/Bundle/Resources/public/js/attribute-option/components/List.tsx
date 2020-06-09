import React, {useEffect, useState} from 'react';
import useAttributeOptions from '../hooks/useAttributeOptions';
import {AttributeOption} from '../model';
import ToggleButton from './ToggleButton';
import ListItem from './ListItem';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import NewOptionPlaceholder from './NewOptionPlaceholder';
import {useAttributeContext} from '../contexts';

interface ListProps {
    selectAttributeOption: (selectedOptionId: number | null) => void;
    showNewOptionForm: (isDisplayed: boolean) => void;
    selectedOptionId: number | null;
    deleteAttributeOption: (attributeOptionId: number) => void;
}

const List = ({selectAttributeOption, selectedOptionId, showNewOptionForm, deleteAttributeOption}: ListProps) => {
    const attributeOptions = useAttributeOptions();
    const translate = useTranslate();
    const attributeContext = useAttributeContext();
    const [showNewOptionPlaceholder, setShowNewOptionPlaceholder] = useState<boolean>(false);
    const [sortedAttributeOptions, setSortedAttributeOptions] = useState<AttributeOption[] | null>(attributeOptions);

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
                    return option1.code.localeCompare(option2.code, undefined , {sensitivity: 'base'});
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

            <div role="attribute-options-list">
                {sortedAttributeOptions !== null && sortedAttributeOptions.map((attributeOption: AttributeOption) => {
                    return (
                        <ListItem
                            key={attributeOption.code}
                            data={attributeOption}
                            selectAttributeOption={onSelectItem}
                            isSelected={selectedOptionId === attributeOption.id}
                            deleteAttributeOption={deleteAttributeOption}
                        />
                    );
                })}

                {showNewOptionPlaceholder === true && (<NewOptionPlaceholder cancelNewOption={cancelNewOption}/>)}
            </div>
        </div>
    );
};

export default List;
