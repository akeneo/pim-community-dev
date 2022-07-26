import {apiFetch} from '../../../api/apiFetch';
import {ProductFiles} from '../model';

const fetchProductFiles = async (): Promise<ProductFiles> => {
    return apiFetch<ProductFiles>(`/supplier-portal/product-files`);
};

export {fetchProductFiles};
