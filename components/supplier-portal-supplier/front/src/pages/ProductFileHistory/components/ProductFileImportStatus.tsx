import React from 'react';
import {Badge} from 'akeneo-design-system';
import {ImportStatus} from '../model/ImportStatus';
import {useIntl} from 'react-intl';

type Props = {hasComments: boolean; importStatus: ImportStatus};
const ProductFileImportStatus = ({hasComments, importStatus}: Props) => {
    const intl = useIntl();
    let level: any = '';
    let trad = '';

    if (ImportStatus.COMPLETED === importStatus) {
        trad = intl.formatMessage({
            defaultMessage: 'approved',
            id: 'TGLUEq',
        });
        level = 'primary';
    } else if (hasComments) {
        trad = intl.formatMessage({
            defaultMessage: 'commented',
            id: '8kbh6K',
        });
        level = 'secondary';
    } else {
        trad = intl.formatMessage({
            defaultMessage: 'submitted',
            id: 'tnjrnS',
        });
        level = 'tertiary';
    }

    return <Badge level={level}>{trad}</Badge>;
};

export {ProductFileImportStatus};
