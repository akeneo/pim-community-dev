import {useEffect, useState} from 'react';
import {useCategoryTreeRoots} from './useCategoryTreeRoots';
import {Category} from '../models/Category';

type UseSelectedTreeResult = [Category | null, (category: Category) => void];

export const useSelectedTree = (): UseSelectedTreeResult => {
    const [selectedTree, setSelectedTree] = useState<Category | null>(null);
    const {data: categoryTreeRoots} = useCategoryTreeRoots();

    useEffect(() => {
        if (selectedTree !== null || categoryTreeRoots === undefined) {
            return;
        }

        if (categoryTreeRoots.length === 0) {
            throw new Error('No tree root found');
        }

        setSelectedTree(categoryTreeRoots[0]);
    }, [selectedTree, categoryTreeRoots, setSelectedTree]);

    return [selectedTree, setSelectedTree];
};
