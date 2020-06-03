import React from 'react';
import useAttributeOptions from '../hooks/useAttributeOptions';
import {AttributeOption} from '../model';
import ToggleButton from './ToggleButton';
import ListItem from './ListItem';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

interface ListProps {
    onSelectAttributeOption: (selectedOptionId: number) => void;
    selectedOptionId: number | null;
}

const List = ({onSelectAttributeOption, selectedOptionId}: ListProps) => {
    const attributeOptions = useAttributeOptions();
    const translate = useTranslate();

    return (
        <div className="AknSubsection AknAttributeOption-list">
            <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
                <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_codes')}</span>
                <div className="AknButton AknButton--micro">{translate('pim_enrich.entity.product.module.attribute.add_option')}</div>
            </div>

            <div>{translate('pim_enrich.entity.attribute.property.auto_option_sorting')}</div>
            <ToggleButton />

            <div role="attribute-options-list">
                {attributeOptions !== null && attributeOptions.map((attributeOption: AttributeOption) => {
                    return (
                        <ListItem
                            key={attributeOption.code}
                            data={attributeOption}
                            onSelectAttributeOption={onSelectAttributeOption}
                            isSelected={selectedOptionId === attributeOption.id}
                        />
                    );
                })}
            </div>
        </div>
    );
};

export default List;
