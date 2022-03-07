import {useEffect, useState} from "react";
import {useRoute} from "@akeneo-pim-community/shared";

type SupplierRow = {
    code: string;
    label: string;
    contributorsCount: number;
};

export const SUPPLIERS_PER_PAGE = 50;

const useSuppliers = (search: string, page: number): [SupplierRow[], number, () => void] => {
    const [suppliers, setSuppliers] = useState<SupplierRow[]>(
        []
    );
    const [totalNumberOfSuppliers, setTotalNumberOfSuppliers] = useState<number>(0);

    const getSuppliersRoute = useRoute('onboarder_serenity_supplier_list');

    useEffect(() => {
        (async () => {
            const response = await fetch(`${getSuppliersRoute}?page=${page}&search=${search}`, {
                method: 'GET',
                headers: {},
            });

            const responseBody = await response.json();
            setSuppliers(responseBody['suppliers']);
            setTotalNumberOfSuppliers(responseBody['total']);
        })()
    }, [page, search]);

    return [
        suppliers,
        totalNumberOfSuppliers,
        () => {}
    ];
};

export type {SupplierRow};
export {useSuppliers};
