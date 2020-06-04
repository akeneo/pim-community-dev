import React from 'react';
import {AttributeOption} from '../model';

interface AttributeOptionItemProps {
    data: AttributeOption;
    onSelectAttributeOption: (selectedOptionId: number) => void;
    isSelected: boolean;
    deleteAttributeOption: (optionId: number) => void;
}

const ListItem = ({data, onSelectAttributeOption, isSelected, deleteAttributeOption}: AttributeOptionItemProps) => {
    return (
        <div className={`AknAttributeOption-listItem ${isSelected ? 'AknAttributeOption-listItem--selected' : ''}`} role="attribute-option-item">
            <span className="AknAttributeOption-itemCode" onClick={() => onSelectAttributeOption(data.id)} role="attribute-option-item-label">
                {data.code}
            </span>
            <span className="AknAttributeOption-delete-option-icon" onClick={() => deleteAttributeOption(data.id)}/>
        </div>
    );
};

export default ListItem;
