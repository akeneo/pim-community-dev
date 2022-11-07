export type ProductFileRow = {
    identifier: string;
    uploadedAt: string;
    contributor: string;
    supplier?: string;
    hasUnreadComments: boolean;
};
