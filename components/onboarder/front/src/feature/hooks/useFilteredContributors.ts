import {ContributorEmail} from '../models';
import {useMemo} from "react";
import {useDebounce} from "@akeneo-pim-community/shared";

const useFilteredContributors = (contributors: ContributorEmail[], searchValue: string) => {
    const debouncedSearch = useDebounce(searchValue, 100);

    const filteredContributors = useMemo(() => {
        return contributors.filter((contributor: ContributorEmail) =>
          contributor.toLowerCase().includes(debouncedSearch.toLowerCase().trim())
        );
    }, [contributors, debouncedSearch]);

    return filteredContributors;
};

export {useFilteredContributors};
