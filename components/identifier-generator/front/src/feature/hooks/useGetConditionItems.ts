import {useEffect, useState} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {Conditions, ItemsGroup} from '../models';

const DEFAULT_LIMIT = 20;

enum STATE {
  FIRST_DISPLAY = 'first_display',
  USER_CHANGED_PAGE = 'user_changed_page',
  WAITING = 'waiting',
  FETCHING_IN_PROGRESS = 'fetching_in_progress',
  USER_CHANGED_SEARCH = 'user_changed_search',
  CONDITIONS_CHANGED = 'condition_changed',
}

function mergeItems(items: ItemsGroup[], newPage: ItemsGroup[]) {
  const mergedItems: ItemsGroup[] = [];
  items.forEach(item => mergedItems.push(item));

  newPage.forEach(({id, text, children}) => {
    const existingGroupIndex = mergedItems.findIndex(item => item.id === id);
    if (existingGroupIndex !== -1) {
      children.forEach(child => {
        if ('undefined' === typeof mergedItems[existingGroupIndex].children.find(c => c.id === child.id)) {
          mergedItems[existingGroupIndex].children.push(child);
        }
      });
    } else {
      mergedItems.push({id, text, children});
    }
  });

  return mergedItems;
}

const useGetConditionItems: (
  isOpen: boolean,
  conditions: Conditions,
  limit?: number
) => {
  conditionItems: ItemsGroup[];
  handleNextPage: () => void;
  searchValue: string;
  setSearchValue: (searchValue: string) => void;
} = (isOpen, conditions, limit = DEFAULT_LIMIT) => {
  const router = useRouter();
  const [items, setItems] = useState<ItemsGroup[]>([]);
  const [page, setPage] = useState<number>(1);
  const [areRemainingElements, setAreRemainingElements] = useState<boolean>(true);
  const [state, setState] = useState<STATE>(STATE.FIRST_DISPLAY);
  const [searchValue, setSearchValue] = useState<string>('');
  const [debouncedSearchValue, setDebouncedSearchValue] = useState<string>('');
  const [debounceTimer, setDebounceTimer] = useState<NodeJS.Timeout | undefined>(undefined);

  useEffect(() => {
    setState(STATE.CONDITIONS_CHANGED);
    setItems([]);
    setAreRemainingElements(true);
    setPage(1);
  }, [conditions]);

  useEffect(() => {
    if (
      isOpen &&
      areRemainingElements &&
      (state === STATE.FIRST_DISPLAY ||
        state === STATE.USER_CHANGED_PAGE ||
        state === STATE.USER_CHANGED_SEARCH ||
        state === STATE.CONDITIONS_CHANGED)
    ) {
      setState(STATE.FETCHING_IN_PROGRESS);
      const conditionTypes = conditions.map(condition => condition.type as string);
      const parameters = {
        search: debouncedSearchValue,
        page: state === STATE.USER_CHANGED_SEARCH ? 1 : page,
        limit,
        systemFields: ['family', 'enabled', 'categories'].filter(value => !conditionTypes.includes(value)),
      };
      fetch(router.generate('akeneo_identifier_generator_get_conditions', parameters), {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      }).then(response => {
        response.json().then((result: ItemsGroup[]) => {
          if (result.reduce((prev, group) => prev + group.children.length, 0) < limit) {
            setAreRemainingElements(false);
          }
          setItems(i => (state === STATE.USER_CHANGED_SEARCH ? result : mergeItems(i || [], result)));
          setState(STATE.WAITING);
        });
      });
    }
    // eslint-disable-next-line
  }, [isOpen, conditions, page, state, debouncedSearchValue, areRemainingElements, limit]);

  const handleNextPage = () => {
    if (state === STATE.WAITING && areRemainingElements) {
      setPage(page => page + 1);
      setState(STATE.USER_CHANGED_PAGE);
    }
  };

  const innerSetSearchValue = (value: string) => {
    setSearchValue(value);

    if (debounceTimer) clearTimeout(debounceTimer);

    setDebounceTimer(
      setTimeout(() => {
        setDebouncedSearchValue(value);
        setPage(1);
        setAreRemainingElements(true);
        setState(STATE.USER_CHANGED_SEARCH);
      }, 200)
    );

    /* istanbul ignore next Unable to test this behavior */
    return () => {
      if (debounceTimer) clearTimeout(debounceTimer);
    };
  };

  return {
    conditionItems: items,
    handleNextPage,
    searchValue,
    setSearchValue: innerSetSearchValue,
  };
};

export {useGetConditionItems};
