import {NotificationLevel, useNotify, useRoute, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {useCallback, useEffect, useState} from 'react';
import {ProductFile} from '../models/ProductFile';
import {Comment} from '../models/read/Comment';

const useProductFile = (productFileIdentifier: string) => {
    const getProductFileRoute = useRoute('supplier_portal_retailer_product_files_comment', {productFileIdentifier});
    const saveCommentRoute = useRoute('supplier_portal_retailer_comment_product_file', {productFileIdentifier});
    const [productFile, setProductFile] = useState<ProductFile | null>(null);
    const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);
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
        const productFile = {
            identifier: responseBody.identifier,
            originalFilename: responseBody.originalFilename,
            uploadedAt: responseBody.uploadedAt,
            supplier: responseBody.uploadedBySupplier,
            contributor: responseBody.uploadedByContributor,
            retailerComments: responseBody.retailerComments.map(
                (comment: {author_email: string; content: string; created_at: string}): Comment => {
                    return {
                        authorEmail: comment.author_email,
                        content: comment.content,
                        createdAt: comment.created_at,
                        outgoing: true,
                    };
                }
            ),
            supplierComments: responseBody.supplierComments.map(
                (comment: {author_email: string; content: string; created_at: string}): Comment => {
                    return {
                        authorEmail: comment.author_email,
                        content: comment.content,
                        createdAt: comment.created_at,
                        outgoing: false,
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
                const errors: ValidationError[] = await response.json();
                setValidationErrors(errors);
                notify(
                    NotificationLevel.ERROR,
                    translate('supplier_portal.product_file_dropping.supplier_files.discussion.comment_submit_error')
                );
                return;
            }

            setValidationErrors([]);
            await loadProductFile();
        },
        [saveCommentRoute, notify, translate, loadProductFile]
    );

    useEffect(() => {
        (async () => {
            await loadProductFile();
        })();
    }, [loadProductFile]);

    return [productFile, saveComment, validationErrors] as const;
};

export {useProductFile};
