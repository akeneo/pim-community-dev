declare type SearchBarProps = {
    className?: string;
    placeholder?: string;
    count: number;
    searchValue: string;
    onSearchChange: (searchValue: string) => void;
};
declare const SearchBar: ({ className, placeholder, count, searchValue, onSearchChange }: SearchBarProps) => JSX.Element;
export { SearchBar };
