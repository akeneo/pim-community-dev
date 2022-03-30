export type ContributorEmail = string;

export type Supplier = {
    identifier: string;
    code: string;
    label: string;
    contributors: ContributorEmail[];
};
