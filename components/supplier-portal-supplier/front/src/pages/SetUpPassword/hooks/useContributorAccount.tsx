import {useMutation, useQuery} from 'react-query';
import {ContributorAccount} from '../model';
import {fetchContributorAccount} from '../api/fetchContributorAccount';
import {savePassword} from '../api/savePassword';
import {routes} from '../../routes';
import {useHistory} from 'react-router-dom';
import {useToaster} from '../../../utils/toaster';
import {useIntl} from 'react-intl';
import {BadRequestError} from '../../../api/BadRequestError';
import {useState} from 'react';

const useContributorAccount = (accessToken: string) => {
    const history = useHistory();
    const notify = useToaster();
    const intl = useIntl();
    const [passwordHasErrors, setPasswordHasErrors] = useState(false);

    const {error: loadingError, data: contributorAccount} = useQuery<ContributorAccount, Error>(
        'fetchContributorAccount',
        () => fetchContributorAccount(accessToken)
    );

    const mutation = useMutation(savePassword);

    const submitPassword = async (password: string, hasConsentToPrivacyPolicy: boolean) => {
        if (!contributorAccount) {
            return;
        }

        try {
            await mutation.mutateAsync({
                contributorAccountIdentifier: contributorAccount.id,
                plainTextPassword: password,
                consent: hasConsentToPrivacyPolicy,
            });
            notify(
                intl.formatMessage({
                    id: 'DjV3C4',
                    defaultMessage:
                        'Your account has been successfully activated, you can now log into the application.',
                }),
                'success'
            );
            history.push(routes.login);
        } catch (error) {
            if (error instanceof BadRequestError) {
                setPasswordHasErrors(true);
            }
        }
    };

    return {
        loadingError,
        contributorAccount,
        submitPassword,
        passwordHasErrors,
    };
};

export {useContributorAccount};
