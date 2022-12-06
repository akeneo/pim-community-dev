import {apiFetch} from '../../../api/apiFetch';
import {ProductFile} from '../model/ProductFile';
import {Comment as CommentReadModel, Comment} from '../model/Comment';

export type ProductFiles = {
    product_files: ProductFile[];
    totalNumberOfProductFiles: number;
    totalSearchResults: number;
};

const fetchProductFiles = async (page: number, searchValue: string): Promise<ProductFiles> => {
    const response: any = await apiFetch(`/supplier-portal/product-file/?page=${page}&search=${searchValue}`);

    const productFiles = response.product_files.map((item: any) => {
        const retailerComments = item.retailerComments.map(
            (comment: {author_email: string; content: string; created_at: string}): Comment => {
                return {
                    authorEmail: comment.author_email,
                    content: comment.content,
                    createdAt: comment.created_at,
                    outgoing: false,
                };
            }
        );
        const supplierComments = item.supplierComments.map(
            (comment: {author_email: string; content: string; created_at: string}): Comment => {
                return {
                    authorEmail: comment.author_email,
                    content: comment.content,
                    createdAt: comment.created_at,
                    outgoing: true,
                };
            }
        );
        const comments: CommentReadModel[] = retailerComments
            .concat(supplierComments)
            .sort(
                (a: CommentReadModel, b: CommentReadModel) =>
                    new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime()
            );
        let displayNewMessageIndicatorPill: boolean = false;
        if (0 < comments.length) {
            const lastReadAtTimestamp: number | null =
                null !== item.supplierLastReadAt ? Date.parse(item.supplierLastReadAt) : null;

            if (null === lastReadAtTimestamp) {
                displayNewMessageIndicatorPill = true;
            } else {
                const lastComment: CommentReadModel = comments.slice(-1)[0];
                const lastCommentTimestamp: number = Date.parse(lastComment.createdAt);
                if (lastCommentTimestamp > lastReadAtTimestamp) {
                    displayNewMessageIndicatorPill = true;
                }
            }
        }

        return {
            identifier: item.identifier,
            filename: item.originalFilename,
            contributor: item.uploadedByContributor,
            uploadedAt: item.uploadedAt,
            comments: comments,
            supplierLastReadAt: item.supplierLastReadAt,
            displayNewMessageIndicatorPill: displayNewMessageIndicatorPill,
            importStatus: item.importStatus,
        };
    });

    return {
        product_files: productFiles,
        totalNumberOfProductFiles: response.total_number_of_product_files,
        totalSearchResults: response.total_search_results,
    };
};

export {fetchProductFiles};
