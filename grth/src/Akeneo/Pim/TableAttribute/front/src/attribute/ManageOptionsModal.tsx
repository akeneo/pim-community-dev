import React from 'react';
import {
  AddingValueIllustration,
  Button,
  Field,
  getColor,
  Helper,
  LoaderIcon,
  Modal,
  Pagination,
  Placeholder,
  Search,
  SectionTitle,
  Table,
  TextInput,
  useBooleanState,
  uuid,
  AkeneoThemedProps,
} from 'akeneo-design-system';
import {getLabel, LocaleCode, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeOption, SelectColumnDefinition, SelectOption, TableAttribute} from '../models';
import {TwoColumnsLayout} from './TwoColumnsLayout';
import {FieldsList} from '../shared';
import styled from 'styled-components';
import {ManageOptionsRow} from './ManageOptionsRow';
import {LocaleSwitcher} from './LocaleSwitcher';
import {DeleteOptionModal} from './DeleteOptionModal';
import {SelectOptionRepository} from '../repositories';
import {ImportOptionsButton} from './ImportOptionsButton';
import {useActivatedLocales} from './useActivatedLocales';

const TableContainer = styled.div<{withSticky: boolean} & AkeneoThemedProps>`
  height: calc(100vh - ${({withSticky}) => (withSticky ? 270 : 340)}px);
  overflow: auto;
`;

const ScrollableFieldList = styled(FieldsList)`
  height: calc(100vh - 270px);
  overflow: auto;
`;

const OptionsTwoColumnsLayout = styled(TwoColumnsLayout)`
  width: 1200px;
  height: calc(100vh - 150px);
`;

const ManageOptionsSectionTitle = styled(SectionTitle.Title)`
  flex-grow: 1;
  flex-basis: 400px;
`;

const ManageOptionsSearch = styled(Search)`
  border-bottom-color: ${getColor('grey', 140)};
`;

type ManageOptionsModalProps = {
  limit?: number;
  onClose: () => void;
  attribute: TableAttribute;
  columnDefinition: SelectColumnDefinition;
  onChange: (options: SelectOption[]) => void;
  confirmLabel?: string;
};

export type SelectOptionWithId = SelectOption & {
  id: string;
  isNew: boolean;
};

const emptySelectOption: SelectOptionWithId = {id: uuid(), code: '', labels: {}, isNew: true};

const OPTIONS_PER_PAGE = 20;
const LIMIT_OPTIONS = 20000;
const ManageOptionsModal: React.FC<ManageOptionsModalProps> = ({
  onClose,
  attribute,
  columnDefinition,
  onChange,
  limit = LIMIT_OPTIONS,
  confirmLabel,
}) => {
  const userContext = useUserContext();
  const router = useRouter();
  const translate = useTranslate();
  const activatedLocales = useActivatedLocales();

  const [page, setPage] = React.useState<number>(1);
  const [violations, setViolations] = React.useState<{[optionId: string]: string[]}>({});

  const [options, setOptions] = React.useState<SelectOptionWithId[]>();
  const [selectedOptionIndex, setSelectedOptionIndex] = React.useState<number | undefined>(undefined);
  const [currentLocaleCode, setCurrentLocaleCode] = React.useState<LocaleCode>(userContext.get('catalogLocale'));
  const [searchValue, setSearchValue] = React.useState<string>('');
  const [filteredOptionsIds, setFilteredOptionsIds] = React.useState<{[optionId: string]: boolean}>({});
  const [isDeleteOptionModalOpen, openDeleteOptionModal, closeDeleteOptionModal] = useBooleanState();
  const [indexToRemove, setIndexToRemove] = React.useState<number | undefined>();
  const [scrollToBottom, doScrollToBottom, doNotScrollToBottom] = useBooleanState(false);
  const [isDirty, setDirty] = useBooleanState(false);

  const lastCodeInputRef = React.useRef<HTMLInputElement>();
  const lastLabelInputRef = React.useRef<HTMLInputElement>();
  const newCodeInputRef = React.useRef<HTMLInputElement>();
  const newLabelInputRef = React.useRef<HTMLInputElement>();
  const tableContainerRef = React.useRef<HTMLDivElement>(null);

  const columnLabel = getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code);
  const canSave = Object.keys(violations).length === 0;
  const currentOption =
    typeof selectedOptionIndex === 'undefined' || !options ? undefined : options[selectedOptionIndex];
  const filteredOptions = (options || []).filter(option => !!filteredOptionsIds[option.id]);

  React.useEffect(() => {
    const initializeOptions = (newOptions: SelectOption[]) => {
      const optionsWithId = newOptions.map(option => {
        return {...option, id: uuid(), isNew: false};
      });
      setOptions(optionsWithId);
      setFilteredOptionsIds(
        optionsWithId.reduce((newFilteredOptionIds, option) => {
          newFilteredOptionIds[option.id] = true;

          return newFilteredOptionIds;
        }, {} as {[optionId: string]: boolean})
      );
    };
    if (typeof columnDefinition.options === 'undefined') {
      SelectOptionRepository.findFromColumn(router, attribute.code, columnDefinition.code).then(fetchOptions => {
        if (fetchOptions === null) {
          initializeOptions([]);
        } else {
          initializeOptions(fetchOptions);
        }
      });
    } else {
      initializeOptions(columnDefinition.options);
    }
  }, []);

  React.useEffect(() => {
    if (typeof indexToRemove === 'undefined' || !options) {
      return;
    }
    if (options[indexToRemove]?.isNew) {
      handleDelete();
    } else {
      openDeleteOptionModal();
    }
  }, [indexToRemove]);

  React.useEffect(() => {
    if (scrollToBottom) {
      if (filteredOptions && filteredOptions.length > 0) {
        const option = filteredOptions[filteredOptions.length - 1];
        if (typeof option.labels[currentLocaleCode] !== 'undefined') {
          lastLabelInputRef.current?.focus();
        } else {
          lastCodeInputRef.current?.focus();
        }
      }
      if (typeof tableContainerRef?.current !== 'undefined' && tableContainerRef.current) {
        tableContainerRef.current.scrollTop = tableContainerRef.current.scrollHeight;
      }
    }
  }, [options?.length]);

  const setOptionsAndValidate = (newOptions: SelectOptionWithId[]) => {
    setOptions([...newOptions]);

    const duplicates: {[code: string]: boolean} = {};
    const codes: {[code: string]: boolean} = {};
    newOptions
      .map(option => option.code)
      .forEach(optionCode => {
        if (optionCode in codes) {
          duplicates[optionCode] = true;
        } else {
          codes[optionCode] = true;
        }
      });

    const newViolations: {[optionId: string]: string[]} = {};
    newOptions
      .filter(option => option.isNew)
      .forEach(option => {
        const violationsForOption = [];
        if (option.code === '') {
          violationsForOption.push(translate('pim_table_attribute.validations.column_code_must_be_filled'));
        }
        if (option.code !== '' && !/^[a-zA-Z0-9_]+$/.exec(option.code)) {
          violationsForOption.push(translate('pim_table_attribute.validations.invalid_column_code'));
        }
        if (option.code !== '' && duplicates[option.code]) {
          violationsForOption.push(translate('pim_table_attribute.validations.duplicated_select_code'));
        }
        if (violationsForOption.length > 0) {
          newViolations[option.id] = violationsForOption;
        }
      });
    setViolations(newViolations);
  };

  const handleOptionChange = (index: number, option: SelectOptionWithId) => {
    if (options) {
      (options || [])[index] = option;
      setOptionsAndValidate(options);
      setDirty();
    }
  };

  const handleConfirm = () => {
    if (canSave && options) {
      onChange(
        options.map(option => {
          // eslint-disable-next-line @typescript-eslint/no-unused-vars
          const {id, isNew, ...rest} = option;
          return rest;
        })
      );
    }
    onClose();
  };

  const handleAddOption = (option: SelectOptionWithId) => {
    const newOption = {...option, id: uuid()};
    const newOptions = [...(options || []), newOption];
    setOptionsAndValidate(newOptions);
    const newFilteredOptionIds = {...filteredOptionsIds, [newOption.id]: true};
    setFilteredOptionsIds(newFilteredOptionIds);
    setPage(Math.ceil(Object.keys(newFilteredOptionIds).length / OPTIONS_PER_PAGE));
    option.code = '';
    option.labels = {};
    if (newCodeInputRef.current) newCodeInputRef.current.value = '';
    if (newLabelInputRef.current) newLabelInputRef.current.value = '';
    doScrollToBottom();
  };

  const handleDelete = () => {
    if (typeof indexToRemove !== 'undefined' && options) {
      const idToRemove = options[indexToRemove]?.id;
      const tempValue = [...(options || [])];
      tempValue.splice(indexToRemove, 1);
      setOptionsAndValidate(tempValue);
      setIndexToRemove(undefined);
      doNotScrollToBottom();

      if (idToRemove) {
        delete filteredOptionsIds[idToRemove];
        setFilteredOptionsIds({...filteredOptionsIds});
      }

      const filteredOptions = (tempValue || []).filter(option => !!filteredOptionsIds[option.id]);
      const pageCount = Math.ceil(filteredOptions.length / OPTIONS_PER_PAGE);
      if (page > pageCount) {
        doScrollToBottom();
        setPage(page - 1);
      }
    }
  };

  const handleSearchChange = (searchValue: string) => {
    setSearchValue(searchValue);
    setFilteredOptionsIds(
      (options || []).reduce((newFilteredOptionIds, option) => {
        if ([option.code].concat(Object.values(option.labels)).some(str => str.includes(searchValue))) {
          newFilteredOptionIds[option.id] = true;
        }

        return newFilteredOptionIds;
      }, {} as {[optionId: string]: boolean})
    );
    setPage(1);
    setSelectedOptionIndex(undefined);
  };

  const handleCloseManageOptions = () => {
    if (
      !isDeleteOptionModalOpen &&
      (!isDirty || window.confirm(translate('pim_table_attribute.form.attribute.discard_changes')))
    ) {
      onClose();
    }
  };

  const getRealIndex = (option: SelectOptionWithId) => {
    return options?.findIndex(option2 => option2.id === option.id) as number;
  };
  const isLastOption = (option: SelectOptionWithId) => options && getRealIndex(option) === options?.length - 1;

  const handleLabelChange = (index: number, localeCode: LocaleCode, label: string) => {
    if (options && typeof options[index] !== 'undefined') {
      options[index].labels[localeCode] = label;
      handleOptionChange(index, options[index]);
    }
  };

  const handleCloseDeleteOptionModal = () => {
    setIndexToRemove(undefined);
    closeDeleteOptionModal();
  };

  const LabelTranslations = (
    <>
      <SectionTitle title={translate('pim_common.label_translations')}>
        <SectionTitle.Title>{translate('pim_common.label_translations')}</SectionTitle.Title>
      </SectionTitle>
      {typeof selectedOptionIndex !== 'undefined' && currentOption && (
        <ScrollableFieldList>
          {!activatedLocales && <LoaderIcon />}
          {activatedLocales &&
            activatedLocales
              .filter(locale => locale.code !== currentLocaleCode)
              .map(locale => (
                <Field label={locale.label} key={locale.code} locale={locale.code}>
                  <TextInput
                    key={selectedOptionIndex}
                    onChange={label => handleLabelChange(selectedOptionIndex, locale.code, label)}
                    value={currentOption.labels[locale.code] ?? ''}
                    maxLength={255}
                  />
                </Field>
              ))}
        </ScrollableFieldList>
      )}
    </>
  );

  const handleImportOptions = (attributeOptions: AttributeOption[]) => {
    if (options) {
      const newOptions = [...options];
      const newFilteredOptionIds: {[optionId: string]: boolean} = {};
      attributeOptions.forEach(attributeOption => {
        const existingOptionIndex = (options || []).findIndex(({code}) => code === attributeOption.code);
        if (existingOptionIndex >= 0) {
          newOptions[existingOptionIndex].labels = attributeOption.labels;
        } else if (newOptions.length < limit) {
          const id = uuid();
          newOptions.push({
            ...attributeOption,
            id,
            isNew: true,
          });
          newFilteredOptionIds[id] = true;
        }
      });

      setOptionsAndValidate(newOptions);
      setFilteredOptionsIds({...filteredOptionsIds, ...newFilteredOptionIds});
    }
  };

  return (
    <>
      <Modal closeTitle={translate('pim_common.close')} onClose={handleCloseManageOptions}>
        <Modal.SectionTitle color='brand'>
          {getLabel(attribute.labels, userContext.get('catalogLocale'), attribute.code)}&nbsp;/&nbsp;
          {columnLabel}
        </Modal.SectionTitle>
        <Modal.Title>{translate('pim_table_attribute.form.attribute.manage_options')}</Modal.Title>
        <OptionsTwoColumnsLayout rightColumn={LabelTranslations}>
          <div>
            <SectionTitle title={columnLabel}>
              <ManageOptionsSectionTitle>{columnLabel}</ManageOptionsSectionTitle>
              <ManageOptionsSearch
                searchValue={searchValue}
                onSearchChange={handleSearchChange}
                placeholder={translate('pim_common.search')}
              />
              <LocaleSwitcher
                localeCode={currentLocaleCode}
                onChange={setCurrentLocaleCode}
                locales={activatedLocales || []}
              />
            </SectionTitle>
            {options && options.length >= limit && (
              <Helper level='info'>
                {translate('pim_table_attribute.form.attribute.limit_option_reached', {
                  limit,
                })}
              </Helper>
            )}
            {!options && <LoaderIcon />}
            {options && (
              <>
                {filteredOptions.length > 0 && (
                  <Pagination
                    currentPage={page}
                    totalItems={filteredOptions.length}
                    itemsPerPage={OPTIONS_PER_PAGE}
                    followPage={setPage}
                  />
                )}
                <TableContainer withSticky={options && options.length < limit} ref={tableContainerRef}>
                  <Table>
                    <Table.Header>
                      <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
                      <Table.HeaderCell>
                        {translate('pim_common.code')} {translate('pim_common.required_label')}
                      </Table.HeaderCell>
                      <Table.HeaderCell />
                    </Table.Header>
                    <Table.Body>
                      {filteredOptions.slice((page - 1) * OPTIONS_PER_PAGE, page * OPTIONS_PER_PAGE).map(option => (
                        <ManageOptionsRow
                          codeInputRef={isLastOption(option) ? lastCodeInputRef : undefined}
                          labelInputRef={isLastOption(option) ? lastLabelInputRef : undefined}
                          isSelected={selectedOptionIndex === getRealIndex(option)}
                          onSelect={() => setSelectedOptionIndex(getRealIndex(option))}
                          data-testid={`row-${getRealIndex(option)}`}
                          onChange={(option: SelectOptionWithId) => handleOptionChange(getRealIndex(option), option)}
                          key={option.id}
                          option={option}
                          onDelete={() => setIndexToRemove(getRealIndex(option))}
                          violations={violations[option.id]}
                          localeCode={currentLocaleCode}
                          onLabelEnter={isLastOption(option) ? () => newLabelInputRef.current?.focus() : undefined}
                          onCodeEnter={isLastOption(option) ? () => newCodeInputRef.current?.focus() : undefined}
                        />
                      ))}
                      {options && options.length < limit && (
                        <ManageOptionsRow
                          isSticky={true}
                          codeInputRef={newCodeInputRef}
                          labelInputRef={newLabelInputRef}
                          isSelected={selectedOptionIndex === -1}
                          onSelect={() => setSelectedOptionIndex(-1)}
                          data-testid={'row-new'}
                          onChange={(option: SelectOptionWithId) => handleAddOption(option)}
                          option={emptySelectOption}
                          labelPlaceholder={translate('pim_table_attribute.form.attribute.new_option_placeholder')}
                          localeCode={currentLocaleCode}
                          forceAutocomplete={true}
                        />
                      )}
                    </Table.Body>
                  </Table>
                  {filteredOptions.length === 0 && searchValue !== '' && (
                    <Placeholder
                      illustration={<AddingValueIllustration />}
                      title={translate('pim_table_attribute.form.attribute.no_options')}
                    >
                      {translate('pim_table_attribute.form.attribute.please_try_again')}
                    </Placeholder>
                  )}
                  {filteredOptions.length === 0 && searchValue === '' && (
                    <Placeholder
                      illustration={<AddingValueIllustration />}
                      title={translate('pim_table_attribute.form.attribute.no_options_helper')}
                    />
                  )}
                </TableContainer>
              </>
            )}
          </div>
          <Modal.TopRightButtons>
            <ImportOptionsButton onClick={handleImportOptions} batchSize={25} />
            <Button level='primary' onClick={handleConfirm} disabled={!canSave}>
              {confirmLabel ? confirmLabel : translate('pim_common.confirm')}
            </Button>
          </Modal.TopRightButtons>
        </OptionsTwoColumnsLayout>
      </Modal>
      {isDeleteOptionModalOpen && typeof indexToRemove !== 'undefined' && options && (
        <DeleteOptionModal
          close={handleCloseDeleteOptionModal}
          onDelete={handleDelete}
          optionCode={options[indexToRemove]?.code ?? ''}
          isFirstColumn={attribute.table_configuration[0].code === columnDefinition.code}
          attributeLabel={getLabel(attribute.labels, userContext.get('catalogLocale'), attribute.code)}
        />
      )}
    </>
  );
};

export {ManageOptionsModal, LIMIT_OPTIONS};
