import {useState} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {GroupType} from '../../models';

const UserContext = require('pim/user-context');

export type GroupTypes = {
    total: number;
    currentPage: number;
    list: GroupType[];
};

export const useGroupTypes = () => {
    const [groupTypes, setGroupTypes] = useState<GroupTypes | null>(null);
    const localeCode = UserContext.get('catalogLocale');
    const router = useRouter();

    const search = async (searchString: string, sortDirection: string, page: number) => {
        //TODO cf useAssociationTypes
    };

    return {groupTypes, search};
};
