export type SupplierFileRow = {
  identifier: string;
  uploadedAt: string;
  contributor: string;
  supplier?: string;
  status: 'To download' | 'Downloaded';
};
