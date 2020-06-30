import React from 'react';

import List from './List';
import Edit from './Edit';
import New from './New';
import EmptyAttributeOptionsList from './EmptyAttributeOptionsList';
import {useAttributeOptionsContext} from '../hooks';

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

            {(isEmpty() && !isCreating()) && <EmptyAttributeOptionsList />}

            {(isEmpty() && !isCreating()) || <List />}

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
