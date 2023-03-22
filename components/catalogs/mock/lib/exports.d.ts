declare type CatalogList = (props: {
    owner: string;
    onCatalogClick: (id: string) => void;
}) => null;
declare type CatalogEdit = (props: {
    id: string;
    form: never;
    headerContextContainer: HTMLDivElement | undefined;
}) => null;
declare type useCatalog = (id: string) => {
    isLoading: boolean;
    isError: boolean;
    data: undefined | {
        id: string;
        name: string;
        enabled: boolean;
        owner_username: string;
    };
    error: null;
};
declare type useCatalogForm = (id: string) => [
    {
        values: {};
        dispatch: () => void;
        errors: {};
    } | undefined,
    () => Promise<boolean>,
    boolean
];
export declare const CatalogList: CatalogList;
export declare const CatalogEdit: CatalogEdit;
export declare const useCatalog: useCatalog;
export declare const useCatalogForm: useCatalogForm;
export {};
