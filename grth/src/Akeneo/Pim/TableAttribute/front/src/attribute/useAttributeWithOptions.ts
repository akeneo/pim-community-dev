import React from 'react';
import {useRouter, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeWithOptions} from '../models';
import {AttributeFetcher} from '../fetchers';

const useAttributeWithOptions: (
  isOpen: boolean,
  batchSize: number
) => {attributes: AttributeWithOptions[]; onNextPage: () => void} = (isOpen, batchSize) => {
  const router = useRouter();
  const userContext = useUserContext();
  const locale = userContext.get('catalogLocale');
  const [attributes, setAttributes] = React.useState<AttributeWithOptions[]>([]);
  const [currentPage, setCurrentPage] = React.useState<number>(0);
  const [isFetching, setIsFetching] = React.useState<boolean>(false);

  React.useEffect(() => {
    if (isOpen) {
      setIsFetching(true);
      AttributeFetcher.findAttributeWithOptions(router, locale, currentPage * batchSize, batchSize).then(
        newAttributes => {
          const isThereNextPages = newAttributes.length === batchSize;
          setAttributes(attributes => [...(attributes || []), ...newAttributes]);
          setIsFetching(!isThereNextPages);
        }
      );
    }
  }, [isOpen, locale, router, currentPage]);

  const onNextPage = () => {
    if (!isFetching) {
      setCurrentPage(previousValue => previousValue + 1);
    }
  };

  return {
    attributes,
    onNextPage,
  };
};

export {useAttributeWithOptions};
