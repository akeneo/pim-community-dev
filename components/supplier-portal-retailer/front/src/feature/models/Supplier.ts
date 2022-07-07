export const LABEL_AND_CODE_MAX_LENGTH = 200;

export const isValidEmail = (email: string): boolean => {
    const emailRegex = /\S+@\S+\.\S+/;
    return emailRegex.test(email);
};

export type ContributorEmail = string;

export type Supplier = {
    identifier: string;
    code: string;
    label: string;
    contributors: ContributorEmail[];
};
