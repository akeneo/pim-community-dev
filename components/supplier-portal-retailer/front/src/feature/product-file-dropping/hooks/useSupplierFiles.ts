import {useCallback, useEffect, useState} from 'react';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {SupplierFileRow} from '../models/SupplierFileRow';

const useSupplierFiles = (page: number): [SupplierFileRow[], number] => {
    const [totalNumberOfSupplierFiles, setTotalNumberOfSupplierFiles] = useState<number>(page);
    const [supplierFiles, setSupplierFiles] = useState<SupplierFileRow[]>([]);
    const getSupplierFilesRoute = useRoute('supplier_portal_retailer_supplier_files_list');
    const notify = useNotify();
    const translate = useTranslate();

    const loadSupplierFiles = useCallback(async () => {
        const response = await fetch(`${getSupplierFilesRoute}?page=${page}`, {
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
        const supplierFiles: SupplierFileRow[] = responseBody.supplier_files.map((item: any) => {
            return {
                identifier: item.identifier,
                uploadedAt: item.uploadedAt,
                contributor: item.uploadedByContributor,
                supplier: item.uploadedBySupplier,
                status: item.downloaded ? 'Downloaded' : 'To download',
            };
        });
        setSupplierFiles(supplierFiles);
        setTotalNumberOfSupplierFiles(responseBody.total);
    }, [getSupplierFilesRoute, page]); // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        (async () => {
            await loadSupplierFiles();
        })();
    }, [loadSupplierFiles]);

    return [supplierFiles, totalNumberOfSupplierFiles];
};

export {useSupplierFiles};
