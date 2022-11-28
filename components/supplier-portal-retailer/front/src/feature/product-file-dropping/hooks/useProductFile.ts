import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {useCallback, useEffect, useState} from 'react';
import {ProductFile} from '../models/ProductFile';
import {Comment} from '../models/read/Comment';

const useProductFile = (productFileIdentifier: string) => {
    const getProductFileRoute = useRoute('supplier_portal_retailer_product_files_show', {productFileIdentifier});
    const saveCommentRoute = useRoute('supplier_portal_retailer_comment_product_file', {productFileIdentifier});
    const [productFile, setProductFile] = useState<ProductFile | null>(null);
    const [validationError, setValidationError] = useState<string | null>(null);
    const notify = useNotify();
    const translate = useTranslate();

    const loadProductFile = useCallback(async () => {
        const response = await fetch(getProductFileRoute, {method: 'GET'});

        if (!response.ok) {
            notify(
                NotificationLevel.ERROR,
                translate('supplier_portal.product_file_dropping.supplier_files.discussion.loading_error')
            );
            return;
        }

        const responseBody = await response.json();
        const lastReadAtTimestamp: number | null =
            null !== responseBody.retailerLastReadAt ? Date.parse(responseBody.retailerLastReadAt) : null;

        let hasUnreadComments: boolean = false;
        if (0 < responseBody.supplierComments.length) {
            if (null === lastReadAtTimestamp) {
                hasUnreadComments = true;
            } else {
                const lastComment: any = responseBody.supplierComments.slice(-1)[0];
                const lastCommentTimestamp: number = Date.parse(lastComment.created_at);
                if (lastCommentTimestamp > lastReadAtTimestamp) {
                    hasUnreadComments = true;
                }
            }
        }

        const productFile = {
            identifier: responseBody.identifier,
            originalFilename: responseBody.originalFilename,
            uploadedAt: responseBody.uploadedAt,
            supplier: responseBody.uploadedBySupplier,
            contributor: responseBody.uploadedByContributor,
            importStatus: responseBody.importStatus,
            importedAt: responseBody.importDate,
            hasUnreadComments: hasUnreadComments,
            retailerComments: responseBody.retailerComments.map(
                (comment: {author_email: string; content: string; created_at: string}): Comment => {
                    return {
                        authorEmail: comment.author_email,
                        content: comment.content,
                        createdAt: comment.created_at,
                        outgoing: true,
                        isUnread: false,
                    };
                }
            ),
            supplierComments: responseBody.supplierComments.map(
                (comment: {author_email: string; content: string; created_at: string}): Comment => {
                    const commentDate: number = Date.parse(comment.created_at);
                    return {
                        authorEmail: comment.author_email,
                        content: comment.content,
                        createdAt: comment.created_at,
                        outgoing: false,
                        isUnread: null !== lastReadAtTimestamp ? commentDate >= lastReadAtTimestamp : true,
                    };
                }
            ),
        };

        setProductFile(productFile);
    }, [getProductFileRoute, notify, translate]);

    const saveComment = useCallback(
        async (content: string, authorEmail: string) => {
            const response = await fetch(saveCommentRoute, {
                method: 'POST',
                headers: [
                    ['Content-type', 'application/json'],
                    ['X-Requested-With', 'XMLHttpRequest'],
                ],
                body: JSON.stringify({content: content, authorEmail: authorEmail}),
            });

            if (!response.ok) {
                const error: string = await response.json();
                setValidationError(error);
                notify(
                    NotificationLevel.ERROR,
                    translate('supplier_portal.product_file_dropping.supplier_files.discussion.comment_submit_error')
                );
                return;
            }

            setValidationError(null);
            await loadProductFile();
        },
        [saveCommentRoute, notify, translate, loadProductFile]
    );

    useEffect(() => {
        (async () => {
            await loadProductFile();
        })();
    }, [loadProductFile]);

    return [productFile, saveComment, validationError] as const;
};

export {useProductFile};
