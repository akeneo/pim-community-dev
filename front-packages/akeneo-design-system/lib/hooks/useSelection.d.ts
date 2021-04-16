declare type Selection<Type = string> = {
    mode: 'in' | 'not_in';
    collection: Type[];
};
declare const useSelection: <Type = string>(totalCount: number) => readonly [Selection<Type>, boolean | "mixed", (item: Type) => boolean, (item: Type, newValue: boolean) => void, (newValue: boolean) => void, number];
export { useSelection };
export type { Selection };
