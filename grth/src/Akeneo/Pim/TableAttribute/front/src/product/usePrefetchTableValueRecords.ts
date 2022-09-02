import React from 'react';
import {useRouter, useUserContext} from '@akeneo-pim-community/shared';
import {castReferenceEntityColumnDefinition, RecordCode} from '../models';
import {ReferenceEntityRecordRepository} from '../repositories';
import {useAttributeContext} from '../contexts';
import {TableValueWithId} from './TableFieldApp';

const usePrefetchTableValueRecords: (valueData: TableValueWithId) => boolean = valueData => {
  const {attribute} = useAttributeContext();
  const userContext = useUserContext();
  const [isPrefetched, setIsPrefetched] = React.useState<boolean>(false);

  const router = useRouter();
  const isAttributeDefined = typeof attribute !== 'undefined';
  React.useEffect(() => {
    if (isAttributeDefined && attribute) {
      const prefetchPromises = attribute.table_configuration
        .filter(columnDefinition => columnDefinition.data_type === 'reference_entity')
        .map(column => {
          const cellCodes = valueData
            .map(row => row[column.code] as RecordCode | undefined)
            .filter(cellCode => typeof cellCode !== 'undefined') as RecordCode[];
          return ReferenceEntityRecordRepository.search(
            router,
            castReferenceEntityColumnDefinition(column).reference_entity_identifier,
            {
              locale: userContext.get('catalogLocale'),
              channel: userContext.get('catalogScope'),
              codes: cellCodes,
            }
          );
        });
      Promise.all(prefetchPromises).then(() => setIsPrefetched(true));
    }
  }, [isAttributeDefined]);

  return isPrefetched;
};

export {usePrefetchTableValueRecords};
