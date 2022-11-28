import {useCallback, useEffect, useState} from 'react';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {ProductFileRow} from '../models/ProductFileRow';

const useProductFiles = (page: number, searchValue: string): [ProductFileRow[], number, number] => {
    const [totalNumberOfProductFiles, setTotalNumberOfProductFiles] = useState<number>(page);
    const [totalSearchResults, setTotalSearchResults] = useState<number>(page);
    const [productFiles, setProductFiles] = useState<ProductFileRow[]>([]);
    const getProductFilesRoute = useRoute('supplier_portal_retailer_product_files_list');
    const notify = useNotify();
    const translate = useTranslate();

    const loadProductFiles = useCallback(async () => {
        const response = await fetch(`${getProductFilesRoute}?page=${page}&search=${searchValue}`, {
            method: 'GET',
        });
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
                supplier: item.uploadedBySupplier,
                hasUnreadComments: item.hasUnreadComments,
                importStatus: item.importStatus,
                importedAt: item.importDate,
                supplierLabel: item.supplierLabel,
                filename: item.originalFilename,
            };
        });
        setProductFiles(productFiles);
        setTotalNumberOfProductFiles(responseBody.total);
        setTotalSearchResults(responseBody.total_search_results);
    }, [getProductFilesRoute, page, searchValue]); // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        (async () => {
            await loadProductFiles();
        })();
    }, [loadProductFiles]);

    return [productFiles, totalNumberOfProductFiles, totalSearchResults];
};

export {useProductFiles};
