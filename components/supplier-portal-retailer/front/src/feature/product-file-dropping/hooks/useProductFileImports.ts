import {useCallback, useEffect, useState} from 'react';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {ProductImportProfile} from '../models/read/ProductImportProfile';

const useProductFileImports = (isModalOpen: boolean) => {
    const notify = useNotify();
    const translate = useTranslate();
    const listProductFileImportsRoute = useRoute('supplier_portal_retailer_list_product_file_imports');
    const [productFiles, setProductFiles] = useState<ProductImportProfile[]>([]);

    const loadProductFileImports = useCallback(async () => {
        const response = await fetch(listProductFileImportsRoute, {method: 'GET'});

        if (!response.ok) {
            notify(
                NotificationLevel.ERROR,
                translate(
                    'supplier_portal.product_file_dropping.supplier_files.product_files_modal.error_loading_product_import_profiles.title'
                ),
                translate(
                    'supplier_portal.product_file_dropping.supplier_files.product_files_modal.error_loading_product_import_profiles.content'
                )
            );
            return;
        }

        const responseBody = await response.json();
        setProductFiles(responseBody);
    }, [listProductFileImportsRoute, notify, translate]);

    useEffect(() => {
        (async () => {
            if (isModalOpen) {
                await loadProductFileImports();
            }
        })();
    }, [isModalOpen, loadProductFileImports]);

    return {productFiles};
};

export {useProductFileImports};
