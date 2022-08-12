export type CategoryCode = string;

export type Category = {
    id: number;
    code: CategoryCode;
    label: string;
    isLeaf: boolean;
};
