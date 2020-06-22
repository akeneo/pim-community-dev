import React from 'react';

import {useAttributeOptionsContext} from '../contexts';
import List from './List';
import Edit from './Edit';
import New from './New';
import EmptyAttributeOptionsList from './EmptyAttributeOptionsList';

const AttributeOptions = () => {
    const {
        selectedOption,
        isLoading,
        isEmpty,
        isEditing,
        isCreating,
    } = useAttributeOptionsContext();

    return (
        <div className="AknAttributeOption">
            {(isLoading()) && <div className="AknLoadingMask"/>}

            {(isEmpty()) && <EmptyAttributeOptionsList />}

            {(isEmpty()) || <List />}

            {(isEditing()) && (
                <Edit
                    // @ts-ignore
                    option={selectedOption}
                />
            )}

            {(isCreating()) && <New />}
        </div>
    );
};

export default AttributeOptions;
