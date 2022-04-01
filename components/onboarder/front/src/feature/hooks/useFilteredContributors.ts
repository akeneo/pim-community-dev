import {useCallback, useEffect, useState} from 'react';
import {ContributorEmail} from '../models';

const useFilteredContributors = (contributors: ContributorEmail[]) => {
    const [filteredContributors, setFilteredContributors] = useState<ContributorEmail[]>([]);

    useEffect(() => {
        setFilteredContributors(contributors);
    }, [contributors]);

    const search = useCallback(
        (searchValue: string) => {
            setFilteredContributors(
                contributors.filter((contributor: ContributorEmail) =>
                    contributor.toLowerCase().includes(searchValue.toLowerCase().trim())
                )
            );
        },
        [contributors]
    );

    return {
        filteredContributors,
        search,
    };
};

export {useFilteredContributors};
