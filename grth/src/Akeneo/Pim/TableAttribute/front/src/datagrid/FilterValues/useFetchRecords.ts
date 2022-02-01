import React from 'react';
import {castReferenceEntityColumnDefinition, ColumnCode, FilterValue, ReferenceEntityRecord} from '../../models';
import {ReferenceEntityRecordRepository} from '../../repositories';
import {useAttributeContext} from '../../contexts';
import {useRouter, useUserContext} from '@akeneo-pim-community/shared';

const useFetchRecords: (
  value: FilterValue | undefined,
  columnCode?: ColumnCode
) => ReferenceEntityRecord[] | undefined = (value, columnCode) => {
  const router = useRouter();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const catalogScope = userContext.get('catalogScope');
  const {attribute} = useAttributeContext();
  const [records, setRecords] = React.useState<ReferenceEntityRecord[] | undefined>();

  React.useEffect(() => {
    const column = attribute?.table_configuration.find(({code}) => code === columnCode);
    if (column?.data_type !== 'reference_entity' || !Array.isArray(value)) {
      setRecords(undefined);
      return;
    }

    ReferenceEntityRecordRepository.search(
      router,
      castReferenceEntityColumnDefinition(column).reference_entity_identifier,
      {
        codes: value,
        locale: catalogLocale,
        channel: catalogScope,
      }
    ).then(records => setRecords(records));
  }, [router, attribute, value, columnCode, catalogLocale, catalogScope]);

  return records;
};

export {useFetchRecords};
