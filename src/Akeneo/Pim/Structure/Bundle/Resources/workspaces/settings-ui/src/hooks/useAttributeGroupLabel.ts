import {useEffect, useState} from 'react';
import {useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {AttributeGroup} from '../models';
import {getLabel} from 'pimui/js/i18n';

const useAttributeGroupLabel = (group: AttributeGroup): string => {
    const [label, setLabel] = useState<string>(`[${group.code}]`);
    const userContext = useUserContext();

    useEffect(() => {
        if (userContext === null) {
            return;
        }

        setLabel(
            getLabel(group.labels, userContext.get('uiLocale'), group.code)
        );
    }, [group, userContext]);

    return label;
};

export {useAttributeGroupLabel};