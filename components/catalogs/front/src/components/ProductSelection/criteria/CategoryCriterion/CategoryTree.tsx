import React, {FC, useState} from 'react';
import {Tree} from 'akeneo-design-system';
import {useCategoryChildren} from '../../hooks/useCategoryChildren';
import {Category, CategoryCode} from '../../models/Category';

type NodeChildrenProps = {
    categoryCode: CategoryCode;
    selectedCategories: Category[];
    onChange: (values: Category[]) => void;
};

const NodeChildren = ({categoryCode, selectedCategories, onChange}: NodeChildrenProps) => {
    const {data: childrenNodes, isLoading} = useCategoryChildren(categoryCode);

    if (isLoading || childrenNodes === undefined) {
        return null;
    }

    const childrenList = childrenNodes.map(childNode => {
        return (
            <TreeNode
                key={childNode.code}
                category={childNode}
                onChange={onChange}
                selectedCategories={selectedCategories}
                {...childNode}
            />
        );
    });

    return <>{childrenList}</>;
};

type NodeProps = {
    category: Category;
    selectedCategories: Category[];
    onChange: (values: Category[]) => void;
    isRoot?: boolean;
    shouldBeOpened?: boolean;
};

export const TreeNode: FC<NodeProps> = ({category, selectedCategories, onChange, shouldBeOpened = false}) => {
    const [isOpen, setOpen] = useState<boolean>(shouldBeOpened);

    return (
        <Tree
            label={category.label}
            value={category.code}
            isLeaf={category.isLeaf}
            selected={selectedCategories.some(selectedCategory => selectedCategory.code === category.code)}
            onChange={(value, checked) => {
                const newCategorySelection = checked
                    ? [...selectedCategories, category]
                    : [...selectedCategories].filter(category => category.code !== value);

                onChange(newCategorySelection);
            }}
            selectable
            onOpen={() => setOpen(true)}
            onClose={() => setOpen(false)}
        >
            {isOpen ? (
                <NodeChildren
                    key={category.code}
                    categoryCode={category.code}
                    selectedCategories={selectedCategories}
                    onChange={onChange}
                />
            ) : null}
        </Tree>
    );
};
