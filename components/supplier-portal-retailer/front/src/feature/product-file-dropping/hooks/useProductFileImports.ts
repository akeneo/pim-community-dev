import {useCallback, useEffect, useState} from 'react';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {ProductFileImportConfiguration} from '../models/read/ProductFileImportConfiguration';

const useProductFileImports = (isModalOpen: boolean) => {
    const notify = useNotify();
    const translate = useTranslate();
    const listProductFileImportConfigurationsRoute = useRoute(
        'supplier_portal_retailer_list_product_file_import_configurations'
    );
    const importProductFileRoute = useRoute('supplier_portal_retailer_import_product_file');

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

    const importProductFile = async (productFileImportConfigurationCode: string, productFileIdentifier: string) => {
        const response = await fetch(importProductFileRoute, {
            method: 'POST',
            headers: [
                ['Content-type', 'application/json'],
                ['X-Requested-With', 'XMLHttpRequest'],
            ],
            body: JSON.stringify({productFileImportConfigurationCode, productFileIdentifier}),
        });
        if (!response.ok) {
            switch (response.status) {
                case 404:
                    notify(
                        NotificationLevel.ERROR,
                        translate(
                            'supplier_portal.product_file_dropping.supplier_files.product_files_modal.error_launching_product_import.missing_file'
                        )
                    );
                    break;
                case 500:
                    notify(
                        NotificationLevel.ERROR,
                        translate(
                            'supplier_portal.product_file_dropping.supplier_files.product_files_modal.error_launching_product_import.unknown_error.title'
                        ),
                        translate(
                            'supplier_portal.product_file_dropping.supplier_files.product_files_modal.error_launching_product_import.unknown_error.content'
                        )
                    );
                    break;
            }
            return;
        }
        window.location.href = await response.json();
    };

    useEffect(() => {
        (async () => {
            if (isModalOpen) {
                await loadProductFileImportConfigurations();
            }
        })();
    }, [isModalOpen, loadProductFileImportConfigurations]);

    return {productFileImportConfigurations, importProductFile};
};

export {useProductFileImports};
