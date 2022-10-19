import {useQuery} from 'react-query';
import {fetchProductFiles, ProductFiles} from '../api/fetchProductFiles';

const useProductFiles = (page: number) => {
    const {data: productFiles} = useQuery<ProductFiles>(['fetchProductFiles', page], () => fetchProductFiles(page));

    return productFiles;
};

export {useProductFiles};
