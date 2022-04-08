import {useCallback, useEffect, useState} from 'react';
import {ReferenceEntityIdentifierOrCode, ReferenceEntityRecord} from '../models';
import {ReferenceEntityRecordRepository} from '../repositories';
import {useRouter, useUserContext} from '@akeneo-pim-community/shared';
import {RECORD_FETCHER_DEFAULT_LIMIT} from '../fetchers';

type UseRecordProps = {
  itemsPerPage?: number;
  referenceEntityCode?: ReferenceEntityIdentifierOrCode;
  isVisible?: boolean;
  searchValue?: string;
};

const useRecords: (
  props: UseRecordProps
) => {
  items?: ReferenceEntityRecord[];
  isLoading: boolean;
  handleNextPage: () => void;
} = ({
  itemsPerPage = RECORD_FETCHER_DEFAULT_LIMIT,
  referenceEntityCode,
  isVisible = true,
  searchValue = '',
}: UseRecordProps) => {
  const router = useRouter();
  const userContext = useUserContext();
  const [page, setPage] = useState<number>(-1);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [hasNoMoreResult, setHasNoMoreResult] = useState<boolean>(false);
  const [items, setItems] = useState<ReferenceEntityRecord[] | undefined>();
  const locale = userContext.get('catalogLocale');
  const channel = userContext.get('catalogScope');
  const [search, setSearch] = useState<string>('');

  useEffect(() => {
    if (search === searchValue) return;

    setHasNoMoreResult(false);
    setItems(undefined);
    setSearch(searchValue);
  }, [searchValue, search]);

  const loadNextPage = useCallback(
    (forcePage: number = page) => {
      if (hasNoMoreResult || !referenceEntityCode) return;

      setIsLoading(true);
      ReferenceEntityRecordRepository.search(router, referenceEntityCode, {
        search: searchValue,
        page: forcePage + 1,
        itemsPerPage,
        channel,
        locale,
      }).then(response => {
        setIsLoading(false);

        if (response.length < itemsPerPage) {
          setHasNoMoreResult(true);
        }
        setItems((items || []).concat(response));
        setPage(forcePage + 1);
      });
    },
    [channel, hasNoMoreResult, items, itemsPerPage, locale, page, referenceEntityCode, router, searchValue]
  );

  useEffect(() => {
    if (typeof items === 'undefined' && isVisible) {
      loadNextPage(-1);
    }
  }, [items, isVisible, loadNextPage]);

  const handleNextPage = useCallback(() => {
    if (!hasNoMoreResult) {
      loadNextPage();
    }
  }, [hasNoMoreResult, loadNextPage]);

  return {items, isLoading, handleNextPage};
};

export {useRecords};
