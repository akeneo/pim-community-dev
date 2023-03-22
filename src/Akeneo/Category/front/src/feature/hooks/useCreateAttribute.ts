import { useMutation } from "react-query";

type Form = {
    code: string;
    locale: string;
    label: string|null;
    type: string;
    is_localizable: boolean;
    is_scopable: boolean;
};

export const useCreateAttribute = () => {
    return useMutation(
        async (form: Form) => {
            console.log(form);
            return new Promise((res) => setTimeout(() => res({}), 1000));
        },
        {
            onSuccess: () => {
                console.log("success");
            }
        }
    );
};
