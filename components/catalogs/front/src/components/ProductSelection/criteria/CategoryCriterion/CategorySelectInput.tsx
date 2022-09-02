import React, {FC, useRef, useState} from 'react';
import styled from 'styled-components';
import {CategoryCriterionState} from './types';
import {CategorySelection} from './CategorySelection';
import {CategoryTreeSelector} from './CategoryTreeSelector';
import {Category} from '../../models/Category';
import {CategorySelector} from './CategorySelector';
import {useCategoriesByCodes} from '../../hooks/useCategoriesByCodes';
import {Dropdown} from 'akeneo-design-system';
import {useSelectedTree} from '../../hooks/useSelectedTree';

const TreeDropdown = styled(Dropdown.Overlay)`
    margin-top: 0;
    height: 354px;
    width: 400px;
    overflow: auto;
`;

const DropdownParent = styled.div``;

type Props = {
    state: CategoryCriterionState;
    onChange: (state: CategoryCriterionState) => void;
    isInvalid: boolean;
};

const CategorySelectInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const [isOpen, setOpen] = useState<boolean>();
    const dropdownParentRef = useRef<HTMLDivElement>(null);
    const {data: selectedCategories, isLoading} = useCategoriesByCodes(state.value);
    const [selectedTree, setSelectedTree] = useSelectedTree();

    if (isLoading || selectedCategories === undefined) {
        return null;
    }

    const handleCategorySelection = (selectedCategories: Category[]) => {
        onChange({...state, value: selectedCategories.map(category => category.code)});
    };

    return (
        <>
            <CategorySelection
                selectedCategories={selectedCategories}
                onRemove={handleCategorySelection}
                isInvalid={isInvalid}
                onEmptySpaceClick={() => setOpen(true)}
            />
            <DropdownParent ref={dropdownParentRef} />
            {isOpen && selectedTree && (
                <TreeDropdown verticalPosition='down' onClose={() => setOpen(false)} parentRef={dropdownParentRef}>
                    <CategoryTreeSelector selectedTree={selectedTree} onChange={setSelectedTree} />
                    <CategorySelector
                        root={selectedTree}
                        selectedCategories={selectedCategories}
                        onCategorySelect={handleCategorySelection}
                    />
                </TreeDropdown>
            )}
        </>
    );
};

export {CategorySelectInput};
