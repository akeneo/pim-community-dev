const LABEL_AND_CODE_MAX_LENGTH = 200;

const isValidEmail = (email: string) => {
    const emailRegex = /\S+@\S+\.\S+/;
    return emailRegex.test(email);
};

type ContributorEmail = string;

type Supplier = {
    identifier: string;
    code: string;
    label: string;
    contributors: ContributorEmail[];
};

export type {ContributorEmail, Supplier};

export {LABEL_AND_CODE_MAX_LENGTH, isValidEmail};
