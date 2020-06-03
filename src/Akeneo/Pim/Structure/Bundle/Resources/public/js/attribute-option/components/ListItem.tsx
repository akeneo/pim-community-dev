import React from 'react';
import {AttributeOption} from '../model';

interface AttributeOptionItemProps {
    data: AttributeOption;
    onSelectAttributeOption: (selectedOptionId: number) => void;
    isSelected: boolean;
}

const ListItem = ({data, onSelectAttributeOption, isSelected}: AttributeOptionItemProps) => {
    return (
        <div className={`AknAttributeOption-listItem ${isSelected ? 'AknAttributeOption-listItem--selected' : ''}`} role="attribute-option-item">
            <span className="AknAttributeOption-itemCode" onClick={() => onSelectAttributeOption(data.id)} role="attribute-option-item-label">
                {data.code}
            </span>
        </div>
    );
};

export default ListItem;
