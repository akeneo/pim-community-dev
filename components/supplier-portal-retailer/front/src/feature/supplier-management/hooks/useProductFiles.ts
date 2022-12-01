import {useCallback, useEffect, useState} from 'react';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {ProductFileRow} from '../../product-file-dropping/models/ProductFileRow';

const useProductFiles = (supplierIdentifier: string, page: number, searchValue: string): [ProductFileRow[], number] => {
    const [totalNumberOfProductFiles, setTotalNumberOfProductFiles] = useState<number>(page);
    const [productFiles, setProductFiles] = useState<ProductFileRow[]>([]);
    const getProductFilesRoute = useRoute('supplier_portal_retailer_supplier_product_files_list', {
        supplierIdentifier: supplierIdentifier,
        page: page.toString(),
        search: searchValue,
    });
    const notify = useNotify();
    const translate = useTranslate();

    const loadProductFiles = useCallback(async () => {
        const response = await fetch(getProductFilesRoute, {method: 'GET'});
        if (!response.ok) {
            notify(
                NotificationLevel.ERROR,
                translate(
                    'supplier_portal.product_file_dropping.supplier_files.notification.error_loading_supplier_files.title'
                ),
                translate(
                    'supplier_portal.product_file_dropping.supplier_files.notification.error_loading_supplier_files.content'
                )
            );
            return;
        }
        const responseBody = await response.json();
        const productFiles: ProductFileRow[] = responseBody.product_files.map((item: any) => {
            return {
                identifier: item.identifier,
                uploadedAt: item.uploadedAt,
                contributor: item.uploadedByContributor,
                hasUnreadComments: item.hasUnreadComments,
                importStatus: item.importStatus,
                filename: item.originalFilename,
            };
        });
        setProductFiles(productFiles);
        setTotalNumberOfProductFiles(responseBody.total);
    }, [getProductFilesRoute, page, searchValue]); // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        (async () => {
            await loadProductFiles();
        })();
    }, [loadProductFiles]);

    return [productFiles, totalNumberOfProductFiles];
};

export {useProductFiles};
