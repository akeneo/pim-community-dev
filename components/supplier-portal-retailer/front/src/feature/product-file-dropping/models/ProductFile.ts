import {Comment} from './read/Comment';
import {ImportStatus} from './ImportStatus';

export type ProductFile = {
    identifier: string;
    originalFilename: string;
    uploadedAt: string;
    contributor: string;
    supplier: string;
    importStatus: ImportStatus;
    importedAt: string;
    retailerComments: Comment[];
    supplierComments: Comment[];
    hasUnreadComments: boolean;
};
