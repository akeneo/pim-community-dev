import React from 'react';
import {useAttributeOptions} from '../hooks';
import {AttributeOption} from '../model';
import ToggleButton from './ToggleButton';
import AttributeOptionItem from './AttributeOptionItem';

const List = () => {
    const attributeOptions = useAttributeOptions();

    return (
        <div className="AknSubsection AknAttributeOption-list">
            <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
                <span>Option code</span>
                <div className="AknButton AknButton--micro">Add option</div>
            </div>

            <div>Sort automatically options by alphabetical order</div>
            <ToggleButton />

            <div>
                {attributeOptions.map((attributeOption: AttributeOption) => {
                    return (
                        <AttributeOptionItem key={attributeOption.code} data={attributeOption} />
                    );
                })}
            </div>
        </div>
    );
};

export default List;
