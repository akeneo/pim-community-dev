import {useEffect, useState} from 'react';

const fetchItems = async (page: number) => {
  return new Promise(resolve => {
    setTimeout(
      () =>
        resolve([
          {
            id: page + ' name',
            text: page + ' Name',
          },
          {
            id: page + ' collection',
            text: 'Collection',
          },
          {
            id: page + ' description',
            text: 'Description',
          },
          {
            id: page + ' brand',
            text: 'Brand',
          },
          {
            id: page + ' response_time',
            text: 'Response time (ms)',
          },
          {
            id: page + ' variation_name',
            text: 'Variant Name',
          },
          {
            id: page + ' variation_description',
            text: 'Variant description',
          },
          {
            id: page + ' release_date',
            text: 'Release date',
          },
          {
            id: page + ' name',
            text: page + ' Name',
          },
          {
            id: page + ' collection',
            text: 'Collection',
          },
          {
            id: page + ' description',
            text: 'Description',
          },
          {
            id: page + ' brand',
            text: 'Brand',
          },
          {
            id: page + ' response_time',
            text: 'Response time (ms)',
          },
          {
            id: page + ' variation_name',
            text: 'Variant Name',
          },
          {
            id: page + ' variation_description',
            text: 'Variant description',
          },
          {
            id: page + ' release_date',
            text: 'Release date',
          },
          {
            id: page + ' name',
            text: page + ' Name',
          },
          {
            id: page + ' collection',
            text: 'Collection',
          },
          {
            id: page + ' description',
            text: 'Description',
          },
          {
            id: page + ' brand',
            text: 'Brand',
          },
          {
            id: page + ' response_time',
            text: 'Response time (ms)',
          },
          {
            id: page + ' variation_name',
            text: 'Variant Name',
          },
          {
            id: page + ' variation_description',
            text: 'Variant description',
          },
          {
            id: page + ' release_date',
            text: 'Release date',
          },
        ]),
      2000
    );
  });
};

const usePaginatedResults = <Type>(searchValue: string) => {
  const [results, setResults] = useState<Type[]>([]);
  const [page, setPage] = useState<number>(0);

  useEffect(() => {
    fetchItems(page).then(setResults as any);
  }, [page, searchValue]);

  useEffect(() => {
    setPage(0);
  }, [searchValue]);

  return [results, fetchItems];
};

export {usePaginatedResults};
