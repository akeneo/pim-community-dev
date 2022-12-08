import {useEffect, useState} from 'react';
import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
type ProductRow = string[];

type ProductFilePreview = {
    headerRow: string[];
    productRows: ProductRow[];
    columnCount: number;
};

const useProductFilePreview = (productFileIdentifier: string) => {
    const [productFilePreview, setProductFilePreview] = useState<null | ProductFilePreview>(null);
    const getProductFilePreviewRoute = useRoute('supplier_portal_retailer_get_product_file_preview', {
        productFileIdentifier,
    });
    const notify = useNotify();
    const translate = useTranslate();

    useEffect(() => {
        (async () => {
            const response = await fetch(getProductFilePreviewRoute, {method: 'GET'});

            if (!response.ok) {
                switch (response.status) {
                    case 404:
                        notify(
                            NotificationLevel.ERROR,
                            translate(
                                'supplier_portal.product_file_dropping.supplier_files.preview.missing_product_file'
                            )
                        );
                        break;
                    default:
                        notify(
                            NotificationLevel.ERROR,
                            translate(
                                'supplier_portal.product_file_dropping.supplier_files.preview.error_loading_product_file_preview.title'
                            ),
                            translate(
                                'supplier_portal.product_file_dropping.supplier_files.preview.error_loading_product_file_preview.content'
                            )
                        );
                        break;
                }
                return;
            }

            const decodedResponse = await response.json();
            setProductFilePreview({
                headerRow: decodedResponse[1],
                productRows: Object.values<ProductRow>(decodedResponse).slice(1),
                columnCount: decodedResponse[1].length,
            });
        })();
    }, [getProductFilePreviewRoute, notify, translate]);

    return {productFilePreview};
};

export {useProductFilePreview};
