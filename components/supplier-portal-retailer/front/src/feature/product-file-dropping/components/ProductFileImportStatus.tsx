import React from 'react';
import {Badge} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ImportStatus} from "../models/ImportStatus";

type Props = {importStatus: ImportStatus};
const ProductFileImportStatus = ({importStatus}: Props) => {
    const translate = useTranslate();

    const badgeLevelMapping: any = {
        warning: ImportStatus.TO_IMPORT,
        tertiary: ImportStatus.IN_PROGRESS,
        danger: ImportStatus.FAILED,
        primary: ImportStatus.COMPLETED,
    };

    const level: any = Object.keys(badgeLevelMapping).find(key => badgeLevelMapping[key] === importStatus);

    return (
        <Badge level={level}>
            {translate(`supplier_portal.product_file_dropping.supplier_files.import.status.${importStatus}`)}
        </Badge>
    );
};

export {ProductFileImportStatus};
