import React, {useEffect, useState} from "react";

type SupplierRow = {
    code: string;
    label: string;
    contributorsCount: number;
};

export const SUPPLIERS_PER_PAGE = 50;

const useSuppliers = (search: string, page: number): [SupplierRow[], number, () => void] => {
    const [suppliers, setSuppliers] = useState<SupplierRow[]>(
        [...Array(152)].map(
            (_e, i) => (
                {code: `supplier-${i}`, label: `Supplier ${i}`, contributorsCount: Math.floor(Math.random() * 10)}
            )
        )
    );
    const [filteredSuppliers, setFilteredSuppliers] = useState<SupplierRow[]>(suppliers);

    useEffect(() => {
        console.log(page * SUPPLIERS_PER_PAGE);
        setFilteredSuppliers(suppliers
            .filter(supplier => supplier.label.toLocaleLowerCase().includes(search.trim().toLocaleLowerCase()))
            .slice(page === 1 ? 0 : (page - 1) * SUPPLIERS_PER_PAGE, ((page - 1) * SUPPLIERS_PER_PAGE) + SUPPLIERS_PER_PAGE)
        );
    }, [search, page]);

    return [
        filteredSuppliers,
        suppliers.length,
        () => setSuppliers([{code: 'toto', label: "Supplier 1", contributorsCount: 10}])
    ];
};

export type {SupplierRow};
export {useSuppliers};
