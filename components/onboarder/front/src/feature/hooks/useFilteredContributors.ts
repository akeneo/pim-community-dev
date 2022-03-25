import {useCallback, useEffect, useState} from 'react';
import {Contributor} from '../models';

const useFilteredContributors = (contributors: Contributor[]) => {
    const [filteredContributors, setFilteredContributors] = useState<Contributor[]>([]);

    useEffect(() => {
        setFilteredContributors(contributors);
    }, [contributors]);

    const search = useCallback(
        (searchValue: string) => {
            setFilteredContributors(
                contributors.filter((contributor: Contributor) =>
                    contributor.email.toLowerCase().includes(searchValue.toLowerCase().trim())
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
