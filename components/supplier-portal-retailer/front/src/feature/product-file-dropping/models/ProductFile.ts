import {Comment} from './read/Comment';

export type ProductFile = {
    identifier: string;
    originalFilename: string;
    uploadedAt: string;
    contributor: string;
    supplier: string;
    retailerComments: Comment[];
    supplierComments: Comment[];
};
