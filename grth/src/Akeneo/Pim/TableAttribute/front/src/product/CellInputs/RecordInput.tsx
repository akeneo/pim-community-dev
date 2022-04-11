import React, {useCallback, useEffect, useMemo, useState} from 'react';
import {CellInput} from './index';
import {
  AddingValueIllustration,
  Button,
  Dropdown,
  IconButton,
  Image,
  LinkIcon,
  Placeholder,
  TableInput,
  useDebounce,
} from 'akeneo-design-system';
import {useRecords} from '../useRecords';
import {getLabel, useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/shared';
import {CompletenessBadge} from './CompletenessBadge';
import {castReferenceEntityColumnDefinition, RecordCode, ReferenceEntity, ReferenceEntityRecord} from '../../models';
import {ReferenceEntityRecordRepository, ReferenceEntityRepository} from '../../repositories';
import styled from 'styled-components';
import {useLocaleCode} from '../../contexts';

const DEFAULT_IMAGE_PATH = '/bundles/pimui/img/image_default.png';

const EditOptionsContainer = styled.div`
  margin: 10px;
  text-align: center;
`;

const RecordInput: CellInput = ({columnDefinition, highlighted, inError, row, onChange}) => {
  const translate = useTranslate();
  const router = useRouter();
  const security = useSecurity();
  const localeCode = useLocaleCode();

  const [option, setOption] = useState<ReferenceEntityRecord | null | undefined>();
  const [isVisible, setIsVisible] = useState<boolean>(false);
  const [searchValue, setSearchValue] = React.useState<string>('');
  const [closeTick, setCloseTick] = React.useState<boolean>(false);
  const [referenceEntity, setReferenceEntity] = React.useState<ReferenceEntity | undefined | null>();

  const debouncedSearchValue = useDebounce(searchValue, 200);
  const hasEditPermission =
    security.isGranted('akeneo_referenceentity_record_edit') ||
    security.isGranted('akeneo_referenceentity_record_create');
  const referenceEntityCode = castReferenceEntityColumnDefinition(columnDefinition).reference_entity_identifier;
  const cell = row[columnDefinition.code] as RecordCode | undefined;

  const {items, handleNextPage} = useRecords({
    referenceEntityCode,
    searchValue: debouncedSearchValue,
    isVisible,
  });

  useEffect(() => {
    if (items && items.length === 0 && searchValue === '') {
      ReferenceEntityRepository.findByIdentifier(router, referenceEntityCode).then(referenceEntity =>
        setReferenceEntity(referenceEntity || null)
      );
    }
  }, [items, searchValue]);

  useEffect(() => {
    if (!cell) return;

    ReferenceEntityRecordRepository.findByCode(router, referenceEntityCode, cell).then(response => {
      setOption(response);
    });
  }, [cell, referenceEntityCode, router]);

  const value = useMemo(() => {
    if (cell) return getLabel(option?.labels || {}, localeCode, cell);
    return undefined;
  }, [localeCode, cell, option]);

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

  let BottomHelper = undefined;
  if (searchValue === '' && (items || []).length === 0 && typeof referenceEntity !== 'undefined') {
    BottomHelper = (
      <Placeholder
        illustration={<AddingValueIllustration />}
        title={translate('pim_table_attribute.form.product.no_records')}
      >
        {!hasEditPermission &&
          translate('pim_table_attribute.form.product.edit_records_unallowed', {
            referenceEntityLabel: getLabel(referenceEntity?.labels || {}, localeCode, referenceEntityCode),
          })}
      </Placeholder>
    );
  } else if (searchValue !== '' && (items || []).length === 0) {
    BottomHelper = (
      <Placeholder
        illustration={<AddingValueIllustration />}
        title={translate('pim_table_attribute.form.product.no_results')}
      />
    );
  }
  if (hasEditPermission) {
    BottomHelper = (
      <>
        {BottomHelper}
        <EditOptionsContainer>
          <Button
            onClick={() => {
              setCloseTick(!closeTick);
              router.redirectToRoute('akeneo_reference_entities_reference_entity_edit', {
                identifier: referenceEntityCode,
                tab: 'record',
              });
            }}
            ghost
            level='secondary'
          >
            {translate('pim_table_attribute.form.product.manage_records')}
          </Button>
        </EditOptionsContainer>
      </>
    );
  }

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
      onOpenChange={setIsVisible}
      bottomHelper={BottomHelper}
      closeTick={closeTick}
    >
      {items?.map(record => {
        const label = getLabel(record.labels, localeCode, record.code);
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
