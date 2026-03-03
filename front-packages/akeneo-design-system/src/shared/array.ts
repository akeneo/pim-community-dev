interface ArrayUniqueInterface {
  (arrayWithDuplicatedItems: string[]): string[];
  (arrayWithDuplicatedItems: number[]): number[];
  <T>(arrayWithDuplicatedItems: T[], comparator: (first: T, second: T) => boolean): T[];
}

const arrayUnique: ArrayUniqueInterface = <T>(
  arrayWithDuplicatedItems: T[],
  comparator?: (first: T, second: T) => boolean
): T[] => {
  if (undefined === comparator) return Array.from(new Set(arrayWithDuplicatedItems));

  return arrayWithDuplicatedItems.reduce((uniqueItems: T[], current: T) => {
    if (uniqueItems.some(item => comparator(item, current))) {
      return uniqueItems;
    }

    return [...uniqueItems, current];
  }, []);
};

export {arrayUnique};
