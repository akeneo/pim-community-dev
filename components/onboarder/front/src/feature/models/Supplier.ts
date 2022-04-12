export type Contributors = {
    [id: number]: string;
};

export type Supplier = {
    identifier: string;
    code: string;
    label: string;
    contributors: Contributors;
};
