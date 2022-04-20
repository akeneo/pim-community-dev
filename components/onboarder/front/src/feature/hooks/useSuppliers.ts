import {useCallback, useEffect, useState} from 'react';
import {useNotify, useRoute, useTranslate, NotificationLevel} from '@akeneo-pim-community/shared';

export type SupplierRow = {
    identifier: string;
    code: string;
    label: string;
    contributorsCount: number;
};

export const SUPPLIERS_PER_PAGE = 50;

const useSuppliers = (search: string, page: number): [SupplierRow[], number, () => void] => {
    const [suppliers, setSuppliers] = useState<SupplierRow[]>([]);
    const [totalNumberOfSuppliers, setTotalNumberOfSuppliers] = useState<number>(page);
    const notify = useNotify();
    const translate = useTranslate();

    const getSuppliersRoute = useRoute('onboarder_serenity_supplier_list');
    const loadSuppliers = useCallback(async () => {
        const response = await fetch(`${getSuppliersRoute}?page=${page}&search=${search}`, {
            method: 'GET',
        });

        if (!response.ok) {
            notify(
                NotificationLevel.ERROR,
                translate('onboarder.supplier.supplier_list.notification.error.title'),
                translate('onboarder.supplier.supplier_list.notification.error.content')
            );

            return;
        }

        const responseBody = await response.json();
        setSuppliers(responseBody['suppliers']);
        setTotalNumberOfSuppliers(responseBody['total']);
    }, [page, search, getSuppliersRoute]); // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        (async () => {
            await loadSuppliers();
        })();
    }, [loadSuppliers]);

    return [suppliers, totalNumberOfSuppliers, loadSuppliers];
};

export {useSuppliers};
