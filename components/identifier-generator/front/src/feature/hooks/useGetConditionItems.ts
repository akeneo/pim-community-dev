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

enum STATE {
  FIRST_DISPLAY,
  USER_CHANGED_PAGE,
  WAITING,
  FETCHING_IN_PROGRESS,
  USER_CHANGED_SEARCH,
}

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

const useGetConditionItems: (isOpen: boolean, conditions: Conditions) => {
  conditionItems: Response[],
  handleNextPage: () => void,
  searchValue: string,
  setSearchValue: (searchValue: string) => void,
} = (isOpen, conditions) => {

  const router = useRouter();
  const [items, setItems] = useState<Response[] | undefined>(undefined);
  const [page, setPage] = useState<number>(1);
  const [areRemainingElements, setAreRemainingElements] = useState<boolean>(true);
  const [state, setState] = useState<STATE>(STATE.FIRST_DISPLAY);
  const [searchValue, setSearchValue] = useState<string>('');
  const [debouncedSearchValue, setDebouncedSearchValue] = useState<string>('');
  const [debounceTimer, setDebounceTimer] = useState<NodeJS.Timeout | undefined>(undefined);

  useEffect(() => {
    if (isOpen && areRemainingElements &&
      (state === STATE.FIRST_DISPLAY || state === STATE.USER_CHANGED_PAGE || state === STATE.USER_CHANGED_SEARCH)) {
      setState(STATE.FETCHING_IN_PROGRESS);
      const conditionTypes = conditions.map(condition => condition.type as string);
      const parameters = {
        search: debouncedSearchValue,
        page: state === STATE.USER_CHANGED_SEARCH ? 1 : page,
        limit: DEFAULT_LIMIT,
        systemFields: ['family', 'enabled'].filter(value => !conditionTypes.includes(value)),
      };
      fetch(router.generate('akeneo_identifier_generator_get_conditions', parameters), {
        method: 'GET',
        headers: [
          ['X-Requested-With', 'XMLHttpRequest'],
        ],
      }).then(response => {
        response.json().then((result: Response[]) => {
          if (result.reduce((prev, group) => prev + group.children.length, 0) < DEFAULT_LIMIT) {
            setAreRemainingElements(false);
          }
          setItems(i => state === STATE.USER_CHANGED_SEARCH ? result : mergeItems(i || [], result));
          setState(STATE.WAITING);
        });
      });
    }
  }, [isOpen, conditions, router, page, state, debouncedSearchValue, areRemainingElements]);

  const handleNextPage = () => {
    if (state === STATE.WAITING) {
      setPage(page => page + 1);
      setState(STATE.USER_CHANGED_PAGE);
    }
  };

  const innerSetSearchValue = (value: string) => {
    setSearchValue(value);

    if (debounceTimer) clearTimeout(debounceTimer);

    setDebounceTimer(setTimeout(() => {
      setDebouncedSearchValue(value);
      setPage(1);
      setAreRemainingElements(true);
      setState(STATE.USER_CHANGED_SEARCH);
    }, 200));

    return () => {
      if (debounceTimer) clearTimeout(debounceTimer);
    };
  };

  return {
    conditionItems: items || [],
    handleNextPage,
    searchValue,
    setSearchValue: innerSetSearchValue,
  };
};

export {useGetConditionItems};
