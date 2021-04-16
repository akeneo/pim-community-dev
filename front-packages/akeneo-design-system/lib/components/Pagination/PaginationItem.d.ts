import { FC } from 'react';
declare const PAGINATION_SEPARATOR = "\u2026";
declare type PaginationItemProps = {
    currentPage: boolean;
    page: string;
    followPage: (page: number) => void;
};
declare const PaginationItem: FC<PaginationItemProps>;
export { PaginationItem, PAGINATION_SEPARATOR };
