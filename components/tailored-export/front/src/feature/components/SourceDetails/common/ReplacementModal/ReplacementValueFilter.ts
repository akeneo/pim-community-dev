type ReplacementValueFilter = {
  searchValue: string;
  page: number;
  codesToInclude: string[] | null;
  codesToExclude: string[] | null;
};

const isDefaultReplacementValueFilter = (filter: ReplacementValueFilter): boolean =>
  1 === filter.page && filter.searchValue === '' && filter.codesToInclude === null && filter.codesToExclude === null;

const getDefaultReplacementValueFilter = (): ReplacementValueFilter => ({
  searchValue: '',
  page: 1,
  codesToInclude: null,
  codesToExclude: null,
});

export type {ReplacementValueFilter};
export {getDefaultReplacementValueFilter, isDefaultReplacementValueFilter};
