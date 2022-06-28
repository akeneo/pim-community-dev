import {apiFetch} from '../../../api/apiFetch';
import {ContributorAccount} from '../model';

const fetchContributorAccount = (accessToken: string): Promise<ContributorAccount> => {
    return apiFetch<ContributorAccount>(`/onboarder-supplier/authentication/contributor-account/${accessToken}`);
};

export {fetchContributorAccount};
