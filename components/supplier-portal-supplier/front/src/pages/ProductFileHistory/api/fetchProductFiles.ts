import {apiFetch} from '../../../api/apiFetch';
import {ProductFile} from '../model/ProductFile';
import {Comment} from '../model/Comment';

const fetchProductFiles = async (): Promise<ProductFile[]> => {
    const response: any = await apiFetch(`/supplier-portal/product-files`);

    return response.map((item: any) => {
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
};

export {fetchProductFiles};
