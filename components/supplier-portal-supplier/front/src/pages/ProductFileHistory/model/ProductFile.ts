import {ImportStatus} from './ImportStatus';

export type ProductFile = {
    identifier: string;
    filename: string;
    contributor: string;
    uploadedAt: string;
    comments: [];
    supplierLastReadAt: string | null;
    displayNewMessageIndicatorPill: boolean;
    importStatus: ImportStatus;
};
