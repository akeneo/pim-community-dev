import {useQuery} from 'react-query';
import {fetchProductFiles, ProductFiles} from '../api/fetchProductFiles';
import {useState} from 'react';

const useProductFiles = (page: number, searchValue: string, setPage: (pageNumber: number) => void) => {
    const [previousSearchValue, setPreviousSearchValue] = useState<string>('');

    const {data: productFiles} = useQuery<ProductFiles>(
        ['fetchProductFiles', page, searchValue],
        async () => {
            const productFiles = await fetchProductFiles(page, searchValue);

            if (searchValue !== previousSearchValue) {
                setPreviousSearchValue(searchValue);
                setPage(1);
            }

            return productFiles;
        },
        {
            placeholderData: {product_files: [], totalNumberOfProductFiles: 0, totalSearchResults: 0},
            keepPreviousData: true,
        }
    );

    return productFiles;
};

export {useProductFiles};
