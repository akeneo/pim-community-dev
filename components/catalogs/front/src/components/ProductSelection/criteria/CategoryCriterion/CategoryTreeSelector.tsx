import React, {FC, useEffect, useState} from 'react';
import styled from 'styled-components';
import {Dropdown, SwitcherButton} from 'akeneo-design-system';
import {Category} from '../../models/Category';
import {useCategoryTreeRoots} from '../../hooks/useCategoryTreeRoots';
import {useTranslate} from '@akeneo-pim-community/shared';

const CategoryTop = styled.div`
    display: flex;
    justify-content: flex-end;
    padding: 0 20px;
`;

const SelectorButton = styled(SwitcherButton)`
    padding: 5px 0;
    margin: 4px 0;
    width: auto;
    min-width: 150px;
`;

type Props = {
    selectedTree: Category | null;
    onChange: (tree: Category) => void;
};

export const CategoryTreeSelector: FC<Props> = ({selectedTree, onChange}) => {
    const translate = useTranslate();
    const [isOpen, setOpen] = useState<boolean>();
    const treeRootsResult = useCategoryTreeRoots();

    useEffect(() => {
        if (selectedTree !== null || treeRootsResult.data === undefined) {
            return;
        }

        if (treeRootsResult.data.length === 0) {
            throw new Error('No tree root found');
        }

        onChange(treeRootsResult.data[0]);
    }, [selectedTree, treeRootsResult.data, onChange]);

    if (treeRootsResult.isLoading || treeRootsResult.data === undefined) {
        return null;
    }

    return (
        <CategoryTop data-testid={'category-tree-selector'}>
            <SelectorButton
                label={translate('akeneo_catalogs.product_selection.criteria.category.category_tree')}
                onClick={() => setOpen(true)}
            >
                {selectedTree?.label}
            </SelectorButton>
            <Dropdown>
                {isOpen && (
                    <Dropdown.Overlay verticalPosition='down' horizontalPosition='left' onClose={() => setOpen(false)}>
                        <Dropdown.ItemCollection role='listbox'>
                            {treeRootsResult.data.map(tree => (
                                <Dropdown.Item
                                    key={tree.id}
                                    role='option'
                                    isActive={tree.code === selectedTree?.code}
                                    onClick={() => {
                                        onChange(tree);
                                        setOpen(false);
                                    }}
                                >
                                    {tree.label}
                                </Dropdown.Item>
                            ))}
                        </Dropdown.ItemCollection>
                    </Dropdown.Overlay>
                )}
            </Dropdown>
        </CategoryTop>
    );
};
