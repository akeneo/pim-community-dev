export const LABEL_AND_CODE_MAX_LENGTH = 200;

export type ContributorEmail = string;

export type Supplier = {
    identifier: string;
    code: string;
    label: string;
    contributors: ContributorEmail[];
};
