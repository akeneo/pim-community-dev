export enum ImportStatus {
    TO_IMPORT = 'to_import',
    IN_PROGRESS = 'in_progress',
    COMPLETED = 'completed',
    FAILED = 'failed',
}

export type ProductFileRow = {
    identifier: string;
    uploadedAt: string;
    contributor: string;
    supplier?: string;
    hasUnreadComments: boolean;
    importStatus: ImportStatus;
};
