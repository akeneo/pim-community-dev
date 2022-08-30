import React, {FC} from 'react';
import styled from 'styled-components';
import {Category} from '../../models/Category';
import {TreeNode} from './CategoryTree';

const TreeContainer = styled.div`
    & > ul {
        margin: 0 20px;
        padding: 0;
    }
`;
type Props = {
    root: Category;
    onCategorySelect: (values: Category[]) => void;
    selectedCategories: Category[];
};

export const CategorySelector: FC<Props> = ({root, onCategorySelect, selectedCategories}) => {
    return (
        <TreeContainer data-testid='category-tree'>
            <TreeNode
                category={root}
                selectedCategories={selectedCategories}
                onChange={onCategorySelect}
                shouldBeOpened
            />
        </TreeContainer>
    );
};
