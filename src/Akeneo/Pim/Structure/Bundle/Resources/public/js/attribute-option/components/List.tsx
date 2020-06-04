import React, {useEffect, useState} from 'react';
import useAttributeOptions from '../hooks/useAttributeOptions';
import {AttributeOption} from '../model';
import ToggleButton from './ToggleButton';
import ListItem from './ListItem';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import NewOptionPlaceholder from './NewOptionPlaceholder';

interface ListProps {
    selectAttributeOption: (selectedOptionId: number | null) => void;
    showNewOptionForm: (isDisplayed: boolean) => void;
    selectedOptionId: number | null;
    deleteAttributeOption: (attributeOptionId: number) => void;
}

const List = ({selectAttributeOption, selectedOptionId, showNewOptionForm, deleteAttributeOption}: ListProps) => {
    const attributeOptions = useAttributeOptions();
    const translate = useTranslate();
    const [showNewOptionPlaceholder, setShowNewOptionPlaceholder] = useState<boolean>(false);

    useEffect(() => {
        if (selectedOptionId !== null) {
            setShowNewOptionPlaceholder(false);
        }
    }, [selectedOptionId]);

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

            <div>{translate('pim_enrich.entity.attribute.property.auto_option_sorting')}</div>
            <ToggleButton />

            <div role="attribute-options-list">
                {attributeOptions !== null && attributeOptions.map((attributeOption: AttributeOption) => {
                    return (
                        <ListItem
                            key={attributeOption.code}
                            data={attributeOption}
                            onSelectAttributeOption={onSelectItem}
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
