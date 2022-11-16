import {uuid} from 'akeneo-design-system';

type SearchAndReplaceValue = {
  uuid: string;
  what: string;
  with: string;
  case_sensitive: boolean;
};

const getDefaultSearchAndReplaceValue = (): SearchAndReplaceValue => ({
  uuid: uuid(),
  what: '',
  with: '',
  case_sensitive: true,
});

const updateByIndex = (
  replacements: (SearchAndReplaceValue | undefined)[],
  newReplacement: SearchAndReplaceValue,
  index: number
): (SearchAndReplaceValue | undefined)[] => {
  const newReplacements = [...replacements];
  newReplacements[index] = newReplacement;

  return newReplacements;
};

const updateByUuid = (
  replacements: (SearchAndReplaceValue | undefined)[],
  newReplacement: SearchAndReplaceValue
): (SearchAndReplaceValue | undefined)[] =>
  replacements.map(replacement =>
    undefined !== replacement && replacement.uuid === newReplacement.uuid ? newReplacement : replacement
  );

const filterOnSearchValue = (
  replacements: (SearchAndReplaceValue | undefined)[],
  searchValue: string
): SearchAndReplaceValue[] =>
  replacements.filter(
    (replacement): replacement is SearchAndReplaceValue =>
      undefined !== replacement &&
      (replacement.what.toLowerCase().includes(searchValue.toLowerCase()) ||
        replacement.with.toLowerCase().includes(searchValue.toLowerCase()))
  );

const filterEmptyReplacements = (replacements: (SearchAndReplaceValue | undefined)[]): SearchAndReplaceValue[] =>
  replacements.filter(
    (replacement): replacement is SearchAndReplaceValue =>
      undefined !== replacement && ('' !== replacement.what || '' !== replacement.with)
  );

export {filterEmptyReplacements, filterOnSearchValue, getDefaultSearchAndReplaceValue, updateByIndex, updateByUuid};
export type {SearchAndReplaceValue};
