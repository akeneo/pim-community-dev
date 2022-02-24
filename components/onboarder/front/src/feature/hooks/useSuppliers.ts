type SupplierRow = {
    code: string;
    label: string;
    contributorsCount: number;
};

const useSuppliers = (search: string, page: number): [SupplierRow[], () => void] => {
    return [[], () => {}];
};

export type {SupplierRow};
export {useSuppliers};
