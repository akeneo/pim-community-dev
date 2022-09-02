import {CellMatcher} from './index';
import {useAttributeContext} from '../../contexts';
import {getLabel, useUserContext} from '@akeneo-pim-community/shared';
import {ReferenceEntityRecordRepository} from '../../repositories';
import {castReferenceEntityColumnDefinition, RecordCode} from '../../models';

const useSearch: CellMatcher = () => {
  const {attribute} = useAttributeContext();
  const userContext = useUserContext();

  return (cell, searchText, columnCode) => {
    const isSearching = searchText !== '';
    if (!attribute || !isSearching || typeof cell === 'undefined') {
      return false;
    }

    const column = attribute.table_configuration.find(columnDefinition => columnDefinition.code === columnCode);
    const referenceEntityIdentifier = column
      ? castReferenceEntityColumnDefinition(column).reference_entity_identifier
      : '';
    const record = ReferenceEntityRecordRepository.getCachedByCode(referenceEntityIdentifier, cell as RecordCode);

    const label = getLabel(record?.labels || {}, userContext.get('catalogLocale'), cell as RecordCode);
    return !!label && label.toLowerCase().includes(searchText.toLowerCase());
  };
};

export default useSearch;
