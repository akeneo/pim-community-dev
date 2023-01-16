import {useEffect, useState} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {Conditions} from '../models';

const DEFAULT_LIMIT = 20;

type Response = {
  id: string;
  text: string;
  children: {
    id: string;
    text: string;
  }[];
};

function mergeItems(items: Response[], newPage: Response[]) {
  const mergedItems: Response[] = [];
  (items || []).forEach(item => mergedItems.push(item));

  newPage.forEach(({id, text, children}) => {
    const existingGroupIndex = mergedItems.findIndex(item => item.id === id);
    if (existingGroupIndex !== -1) {
      mergedItems[existingGroupIndex].children.push(...children);
    } else {
      mergedItems.push({id, text, children});
    }
  });

  return mergedItems;
}

const useGetConditionItems: (conditions: Conditions) => {
  conditionItems: Response[],
  handleNextPage: () => void,
  searchValue: string,
  setSearchValue: (searchValue: string) => void,
} = conditions => {
  const router = useRouter();
  const [page, setPage] = useState<number>(1);
  const [isFetching, setIsFetching] = useState<boolean>(false);
  const [items, setItems] = useState<Response[] | undefined>(undefined);
  const [isAtLastPage, setIsAtLastPage] = useState<boolean>(false);
  const [searchValue, setSearchValue] = useState('');
  const [debouncedSearchValue, setDebouncedSearchValue] = useState('');

  const fetchConditionItems = async () => {
    setIsFetching(true);
    const conditionTypes = conditions.map(condition => condition.type as string);
    const parameters = {
      search: searchValue,
      page: page,
      limit: DEFAULT_LIMIT,
      systemFields: ['family', 'enabled'].filter(value => !conditionTypes.includes(value)),
    };

    const response = await fetch(router.generate('akeneo_identifier_generator_get_conditions', parameters), {
      method: 'GET',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
    });
    const result = await response.json();
    setIsFetching(false);

    return result;
  };

  const handleNextPage = () => {
    if (!isFetching && !isAtLastPage) {
      setPage(page => page + 1);
    }
  };

  const innerSetSearchValue = (value: string) => {
    setSearchValue(value);

    const timer = setTimeout(() => {
      setPage(1);
      setIsAtLastPage(false);
      setDebouncedSearchValue(value);
    }, 200);

    return () => {
      clearTimeout(timer);
    };
  };

  useEffect(() => {
    if (isFetching) return;

    fetchConditionItems().then((newPage: Response[]) => {
      if (newPage.reduce((prev, group) => prev + group.children.length, 0) < DEFAULT_LIMIT) {
        setIsAtLastPage(true);
      }

      if (page === 1) {
        setItems(newPage);
      } else {
        setItems(mergeItems(items || [], newPage));
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [page, debouncedSearchValue, router]);

  return {conditionItems: items || [], handleNextPage, searchValue, setSearchValue: innerSetSearchValue};
};

export {useGetConditionItems};
