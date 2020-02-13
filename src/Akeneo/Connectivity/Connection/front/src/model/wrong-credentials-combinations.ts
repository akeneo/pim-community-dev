export type WrongCredentialsCombinations = {
    [connectionCode: string]: WrongCredentialsCombination;
};

export type WrongCredentialsCombination = {
    code: string;
    users: [
        {
            username: string;
            date: string;
        }
    ];
};
