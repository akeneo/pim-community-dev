import React from 'react';
import {useRouter, useUserContext} from '@akeneo-pim-community/shared';
import {RecordCode} from '../models';
import {ReferenceEntityRecordRepository} from '../repositories';
import {useAttributeContext} from '../contexts';
import {TableValueWithId} from './TableFieldApp';

const usePrefetchTableValueRecords = (valueData: TableValueWithId) => {
  const {attribute} = useAttributeContext();
  const userContext = useUserContext();
  const [isPrefetched, setIsPrefetched] = React.useState<boolean>(false);

  const router = useRouter();
  React.useEffect(() => {
    if (attribute) {
      if (attribute.table_configuration[0].data_type === 'record') {
        const firstCellCodes = valueData.map(row => row[attribute.table_configuration[0].code] as RecordCode);
        ReferenceEntityRecordRepository.search(router, attribute.table_configuration[0].reference_entity_identifier, {
          locale: userContext.get('catalogLocale'),
          channel: userContext.get('catalogScope'),
          codes: firstCellCodes,
        }).then(() => setIsPrefetched(true));
      }
    }
  }, [attribute]);

  return isPrefetched;
};

export {usePrefetchTableValueRecords};
