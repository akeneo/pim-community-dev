import {apiFetch} from '../../../api/apiFetch';
import {ProductFile} from '../model/ProductFile';

const fetchProductFiles = async (): Promise<ProductFile[]> => {
    const response: any = await apiFetch(`/supplier-portal/product-files`);

    return response.map((item: any) => {
        return {
            identifier: item.identifier,
            filename: item.originalFilename,
            contributor: item.uploadedByContributor,
            uploadedAt: item.uploadedAt,
        };
    });
};

export {fetchProductFiles};
