import {useQuery} from 'react-query';
import {fetchProductFiles, ProductFiles} from '../api/fetchProductFiles';

const useProductFiles = (page: number, searchValue: string) => {
    const {data: productFiles} = useQuery<ProductFiles>(
        ['fetchProductFiles', page, searchValue],
        () => fetchProductFiles(page, searchValue),
        {
            placeholderData: {product_files: [], totalNumberOfProductFiles: 0, totalSearchResults: 0},
            keepPreviousData: true,
        }
    );

    return productFiles;
};

export {useProductFiles};
