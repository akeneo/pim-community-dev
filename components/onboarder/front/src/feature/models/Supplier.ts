type Contributor = {};

type Supplier = {
    identifier: string;
    code: string;
    label: string;
    contributors: Contributor[];
}

export type {Supplier};
