import {apiFetch} from '../../../api/apiFetch';
import {ContributorAccount} from '../model';

const fetchContributorAccount = (accessToken: string): Promise<ContributorAccount> => {
    return apiFetch<ContributorAccount>(`/supplier-portal/authentication/contributor-account/${accessToken}`);
};

export {fetchContributorAccount};
