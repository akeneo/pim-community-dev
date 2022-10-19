import {apiFetch} from '../../../api/apiFetch';
import {ProductFile} from '../model/ProductFile';
import {Comment} from '../model/Comment';

export type ProductFiles = {
    product_files: ProductFile[];
    total: number;
};

const fetchProductFiles = async (page: number): Promise<ProductFiles> => {
    const response: any = await apiFetch(`/supplier-portal/product-file/?page=${page}`);

    const productFiles = response.product_files.map((item: any) => {
        return {
            identifier: item.identifier,
            filename: item.originalFilename,
            contributor: item.uploadedByContributor,
            uploadedAt: item.uploadedAt,
            retailerComments: item.retailerComments.map(
                (comment: {author_email: string; content: string; created_at: string}): Comment => {
                    return {
                        authorEmail: comment.author_email,
                        content: comment.content,
                        createdAt: comment.created_at,
                        outgoing: false,
                    };
                }
            ),
            supplierComments: item.supplierComments.map(
                (comment: {author_email: string; content: string; created_at: string}): Comment => {
                    return {
                        authorEmail: comment.author_email,
                        content: comment.content,
                        createdAt: comment.created_at,
                        outgoing: true,
                    };
                }
            ),
        };
    });

    return {product_files: productFiles, total: response.total};
};

export {fetchProductFiles};
