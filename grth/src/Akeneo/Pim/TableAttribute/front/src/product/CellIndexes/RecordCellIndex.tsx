import React from 'react';
import {RecordCode, RecordColumnDefinition, ReferenceEntityRecord} from '../../models';
import {getLabel, useRouter, useUserContext} from '@akeneo-pim-community/shared';
import {useAttributeContext} from '../../contexts';
import {ReferenceEntityRecordRepository} from '../../repositories';

type RecordCellIndexProps = {
  value: RecordCode;
};

const RecordCellIndex: React.FC<RecordCellIndexProps> = ({value}) => {
  const userContext = useUserContext();
  const router = useRouter();
  const catalogLocale = userContext.get('catalogLocale');
  const {attribute} = useAttributeContext();
  const [record, setRecord] = React.useState<ReferenceEntityRecord | undefined | null>();

  React.useEffect(() => {
    if (!attribute) return;
    ReferenceEntityRecordRepository.findByCode(
      router,
      (attribute.table_configuration[0] as RecordColumnDefinition).reference_entity_identifier,
      value
    ).then(record => {
      setRecord(record);
    });
  }, [attribute]);

  if (typeof record === 'undefined') {
    return <div>atta</div>; // TODO
  }

  return (
    <>
      {record === null && <span>non existing</span>}
      {getLabel(record?.labels || {}, catalogLocale, value)}
    </>
  );
};

export {RecordCellIndex};
