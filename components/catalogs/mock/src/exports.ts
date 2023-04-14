type CatalogList = (props: {
    owner: string;
    onCatalogClick: (id: string) => void;
}) => null;
type CatalogEdit = (props: {
    id: string;
    form: never;
    headerContextContainer: HTMLDivElement | undefined;
}) => null;
type useCatalog = (id: string) => {
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
type useCatalogForm = (id: string) => [
    {
        values: {},
        dispatch: () => void,
        errors: {},
    } | undefined,
    () => Promise<boolean>,
    boolean,
];

export const CatalogList: CatalogList = () => null;
export const CatalogEdit: CatalogEdit = () => null;
export const useCatalog: useCatalog = () => ({
    isLoading: false,
    isError: true,
    data: undefined,
    error: null,
});
export const useCatalogForm: useCatalogForm = () => [undefined, () => Promise.reject(), false];