import React from 'react';
import List from './List';
import Edit from './Edit';
import {useSelector} from 'react-redux';
import {AttributeOptionsState} from '../store/store';

const AttributeOptions = () => {
    const attributeOptions = useSelector((state: AttributeOptionsState) => state.attributeOptions);

    return (
        <div className="AknAttributeOption">
            {attributeOptions === null && <div className="AknLoadingMask"/>}
            <List />
            <Edit />
        </div>
    );
};

export default AttributeOptions;
