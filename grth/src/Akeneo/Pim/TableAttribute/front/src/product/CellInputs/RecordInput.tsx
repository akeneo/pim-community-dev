import React, {useCallback, useMemo, useState, useEffect} from 'react';
import {CellInput} from './index';
import {Dropdown, IconButton, Image, LinkIcon, TableInput, useDebounce} from 'akeneo-design-system';
import {useRecords} from '../useRecords';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {CompletenessBadge} from './CompletenessBadge';
import {RecordCode, RecordColumnDefinition, ReferenceEntityRecord} from '../../models';
import {ReferenceEntityRecordRepository} from '../../repositories';

const DEFAULT_IMAGE_PATH = '/bundles/pimui/img/image_default.png';

const RecordInput: CellInput = ({columnDefinition, highlighted, inError, row, onChange}) => {
  const translate = useTranslate();
  const router = useRouter();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');

  const [option, setOption] = useState<ReferenceEntityRecord | null | undefined>();
  const [searchValue, setSearchValue] = React.useState<string>('');
  const debouncedSearchValue = useDebounce(searchValue, 200);

  const referenceEntityCode = (columnDefinition as RecordColumnDefinition).reference_entity_identifier;
  const cell = row[columnDefinition.code] as RecordCode | undefined;

  const {items, handleNextPage} = useRecords({
    referenceEntityCode,
    searchValue: debouncedSearchValue,
  });

  useEffect(() => {
    if (!cell) return;

    ReferenceEntityRecordRepository.findByCode(router, referenceEntityCode, cell).then(response => {
      setOption(response);
    });
  }, [cell, referenceEntityCode, router]);

  const value = useMemo(() => {
    if (cell) return getLabel(option?.labels || {}, catalogLocale, cell);
    return undefined;
  }, [catalogLocale, cell, option]);

  const getImageUrl = useCallback(
    (path?: string) => {
      if (!path) return DEFAULT_IMAGE_PATH;

      const filename = encodeURIComponent(path);
      return router.generate('pim_enrich_media_show', {filename, filter: 'thumbnail_small'});
    },
    [router]
  );

  const getUrl = useCallback(
    (code: RecordCode) => {
      return router.generate('akeneo_reference_entities_record_edit', {
        recordCode: code,
        referenceEntityIdentifier: referenceEntityCode,
        tab: 'enrich',
      });
    },
    [referenceEntityCode, router]
  );

  const createOnClick = useCallback((code: RecordCode) => () => onChange(code), [onChange]);

  const handleClear = () => {
    onChange(undefined);
  };

  return (
    <TableInput.Select
      clearLabel={translate('pim_common.clear')}
      highlighted={highlighted}
      openDropdownLabel={translate('pim_common.open')}
      value={value}
      onClear={handleClear}
      searchPlaceholder={translate('pim_common.search')}
      searchValue={searchValue}
      onSearchChange={setSearchValue}
      searchTitle={translate('pim_common.search')}
      onNextPage={handleNextPage}
      inError={inError}
    >
      {items?.map(record => {
        const label = getLabel(record.labels, catalogLocale, record.code);
        const image = getImageUrl(record.image?.filePath);
        const url = getUrl(record.code);
        return (
          <Dropdown.Item onClick={createOnClick(record.code)} key={record.code}>
            <Image
              src={image}
              alt={record.image?.originalFilename || ''}
              title={record.image?.originalFilename || 'default'}
            />
            <Dropdown.Surtitle label={record.code}>{label}</Dropdown.Surtitle>
            <CompletenessBadge completeness={record.completeness} />
            {url && (
              <IconButton
                icon={<LinkIcon />}
                ghost='borderless'
                level='tertiary'
                href={`#${url}`}
                target='_blank'
                title={url}
              />
            )}
          </Dropdown.Item>
        );
      })}
    </TableInput.Select>
  );
};

export default RecordInput;
