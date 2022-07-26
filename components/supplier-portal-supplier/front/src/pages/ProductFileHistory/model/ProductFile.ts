export type ProductFile = {
    identifier: string;
    originalFilename: string;
    path: string;
    uploadedByContributor: string;
    uploadedAt: string;
};

export type ProductFiles = ProductFile[] | null;
