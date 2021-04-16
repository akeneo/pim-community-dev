interface ArrayUniqueInterface {
    (arrayWithDuplicatedItems: string[]): string[];
    (arrayWithDuplicatedItems: number[]): number[];
    <T>(arrayWithDuplicatedItems: T[], comparator: (first: T, second: T) => boolean): T[];
}
declare const arrayUnique: ArrayUniqueInterface;
export { arrayUnique };
