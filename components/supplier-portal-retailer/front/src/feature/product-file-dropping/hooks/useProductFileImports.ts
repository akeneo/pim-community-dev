import {useCallback, useEffect, useState} from 'react';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {ProductFileImportConfiguration} from '../models/read/ProductFileImportConfiguration';

const useProductFileImports = (isModalOpen: boolean) => {
    const notify = useNotify();
    const translate = useTranslate();
    const listProductFileImportConfigurationsRoute = useRoute(
        'supplier_portal_retailer_list_product_file_import_configurations'
    );
    const [productFileImportConfigurations, setProductFileImportConfigurations] = useState<
        ProductFileImportConfiguration[]
    >([]);

    const loadProductFileImportConfigurations = useCallback(async () => {
        const response = await fetch(listProductFileImportConfigurationsRoute, {method: 'GET'});

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
        setProductFileImportConfigurations(responseBody);
    }, [listProductFileImportConfigurationsRoute, notify, translate]);

    useEffect(() => {
        (async () => {
            if (isModalOpen) {
                await loadProductFileImportConfigurations();
            }
        })();
    }, [isModalOpen, loadProductFileImportConfigurations]);

    return {productFileImportConfigurations};
};

export {useProductFileImports};
