import {useCallback} from 'react';
import {useRoute} from '../../shared/router';
import {useMutation} from 'react-query';
import {CustomAppCredentials} from '../../model/Apps/custom-app-credentials';
import {UseMutationResult} from 'react-query/types/react/types';

export type CustomApp = {
    name: string;
    activateUrl: string;
    callbackUrl: string;
};

type ValidationError = {
    errors: {
        propertyPath: string;
        message: string;
    }[];
};

type Errors = {
    [field in keyof CustomApp | 'limitReached']?: string;
};

export const useCreateCustomApp = (): UseMutationResult<CustomAppCredentials, Errors, CustomApp> => {
    const url = useRoute('akeneo_connectivity_connection_custom_apps_rest_create');
    const request = useCallback(
        async (customApp: CustomApp) => {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(customApp),
            });

            if (!response.ok && response.status !== 422) {
                throw new Error(`${response.status} ${response.statusText}`);
            }

            if (!response.ok && response.status === 422) {
                const validationErrors = (await response.json()) as ValidationError;
                const mappedErrors = validationErrors.errors.reduce(
                    (errors, {propertyPath, message}) => ({
                        ...errors,
                        [propertyPath.length == 0 ? 'limitReached' : propertyPath]: message,
                    }),
                    {}
                );

                return Promise.reject(mappedErrors);
            }

            return await response.json();
        },
        [url]
    );

    return useMutation<CustomAppCredentials, Errors, CustomApp>(request);
};
