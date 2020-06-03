import React from 'react';
import {AttributeOption} from '../model';

interface AttributeOptionItemProps {
    data: AttributeOption;
}

const AttributeOptionItem = ({data}: AttributeOptionItemProps) => {
    return (
        <div className="AknAttributeOption-listItem" role="attribute-option-item">
            <span className="AknAttributeOption-itemCode">{data.code}</span>
        </div>
    );
};

export default AttributeOptionItem;
