export type Contributor = {
    identifier: string;
    email: string;
};

export type Supplier = {
    identifier: string;
    code: string;
    label: string;
    contributors: Contributor[];
};
