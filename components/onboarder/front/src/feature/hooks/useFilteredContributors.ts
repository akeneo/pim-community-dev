import {useCallback, useEffect, useState} from 'react';
import {Contributors} from '../models';

const useFilteredContributors = (contributors: Contributors) => {
    const [filteredContributors, setFilteredContributors] = useState<Contributors>([]);

    useEffect(() => {
        setFilteredContributors(contributors);
    }, [contributors]);

    const search = useCallback(
        (searchValue: string) => {
            const filteredResults = Object.entries(contributors).filter(([_, email]) =>
                email.toLowerCase().includes(searchValue.toLowerCase().trim())
            );
            setFilteredContributors(Object.fromEntries(filteredResults.map(result => result)));
        },
        [contributors]
    );

    return {
        filteredContributors,
        search,
    };
};

export {useFilteredContributors};
