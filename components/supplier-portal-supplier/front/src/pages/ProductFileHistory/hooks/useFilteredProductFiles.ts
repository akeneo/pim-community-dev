import {ProductFile} from '../model/ProductFile';
import {useMemo} from 'react';

const useFilteredProductFiles = (productFiles: ProductFile[], searchValue: string) => {
    return useMemo(() => {
        return productFiles.filter((productFile: ProductFile) =>
            productFile.filename.toLowerCase().includes(searchValue.toLowerCase().trim())
        );
    }, [productFiles, searchValue]);
};

export {useFilteredProductFiles};
