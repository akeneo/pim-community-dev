import React from 'react';
import {
  Button,
  Field,
  Modal,
  SectionTitle,
  Table,
  TextInput,
  uuid,
  LoaderIcon,
  getColor,
  Pagination,
  Search,
  AddingValueIllustration,
  useBooleanState,
} from 'akeneo-design-system';
import {getLabel, Locale, LocaleCode, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {SelectColumnDefinition, SelectOption} from '../models/TableConfiguration';
import {Attribute} from '../models/Attribute';
import {TwoColumnsLayout} from './TwoColumnsLayout';
import {FieldsList} from '../shared/FieldsList';
import styled from 'styled-components';
import {fetchSelectOptions} from '../fetchers/SelectOptionsFetcher';
import {getActivatedLocales} from '../repositories/Locale';
import {ManageOptionsRow} from './ManageOptionsRow';
import {LocaleSwitcher} from './LocaleSwitcher';
import {DeleteOptionModal} from './DeleteOptionModal';

const TableContainer = styled.div`
  height: calc(100vh - 270px);
  overflow: auto;
`;

const OptionsTwoColumnsLayout = styled(TwoColumnsLayout)`
  width: 1200px;
  height: calc(100vh - 150px);
`;

const ManageOptionsBody = styled(Table.Body)`
  & > tr:last-child {
    position: sticky;
    bottom: -1px;
    background-color: ${getColor('white')};
    z-index: 1;

    & > td {
      border-bottom: 1px solid ${getColor('white')};
    }
  }
`;

const ManageOptionsSectionTitle = styled(SectionTitle.Title)`
  flex-grow: 1;
  flex-basis: 400px;
`;

const CenteredHelper = styled.div`
  text-align: center;
  & > * {
    display: block;
    margin: auto;
  }
`;

const ManageOptionsSearch = styled(Search)`
  border-bottom-color: ${getColor('grey', 140)};
`;

type ManageOptionsModalProps = {
  onClose: () => void;
  attribute: Attribute;
  columnDefinition: SelectColumnDefinition;
  onChange: (options: SelectOption[]) => void;
};

export type SelectOptionWithId = SelectOption & {
  id: string;
  isNew: boolean;
};

const emptySelectOption: SelectOptionWithId = {id: uuid(), code: '', labels: {}, isNew: true};

const OPTIONS_PER_PAGE = 20;

const ManageOptionsModal: React.FC<ManageOptionsModalProps> = ({onClose, attribute, columnDefinition, onChange}) => {
  const userContext = useUserContext();
  const router = useRouter();
  const translate = useTranslate();

  const [page, setPage] = React.useState<number>(1);
  const [violations, setViolations] = React.useState<{[optionId: string]: string[]}>({});
  const [activatedLocales, setActivatedLocales] = React.useState<Locale[]>();
  const [options, setOptions] = React.useState<SelectOptionWithId[]>();
  const [selectedOptionIndex, setSelectedOptionIndex] = React.useState<number | undefined>(undefined);
  const [currentLocaleCode, setCurrentLocaleCode] = React.useState<LocaleCode>(userContext.get('catalogLocale'));
  const [searchValue, setSearchValue] = React.useState<string>('');
  const [filteredOptionsIds, setFilteredOptionsIds] = React.useState<{[optionId: string]: boolean}>({});
  const [isDeleteOptionModalOpen, openDeleteOptionModal, closeDeleteOptionModal] = useBooleanState();
  const [indexToRemove, setIndexToRemove] = React.useState<number | undefined>();
  const [scrollToBottom, doScrollToBottom, doNotScrollToBottom] = useBooleanState(false);

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
      fetchSelectOptions(router, attribute.code, columnDefinition.code).then(fetchOptions => {
        if (typeof fetchOptions === 'undefined') {
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

  React.useEffect(() => {
    getActivatedLocales(router).then((activeLocales: Locale[]) => setActivatedLocales(activeLocales));
  }, [router]);

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
          violationsForOption.push(translate('pim_table_attribute.validations.invalid_code'));
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

  const LabelTranslations = (
    <>
      <SectionTitle title={translate('pim_common.label_translations')}>
        <SectionTitle.Title>{translate('pim_common.label_translations')}</SectionTitle.Title>
      </SectionTitle>
      {typeof selectedOptionIndex !== 'undefined' && currentOption && (
        <FieldsList>
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
        </FieldsList>
      )}
    </>
  );

  return (
    <>
      <Modal closeTitle={translate('pim_common.close')} onClose={onClose}>
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
                placeholder={translate('pim_table_attribute.form.attribute.search_placeholder')}
              />
              <LocaleSwitcher
                localeCode={currentLocaleCode}
                onChange={setCurrentLocaleCode}
                locales={activatedLocales || []}
              />
            </SectionTitle>
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
                <TableContainer ref={tableContainerRef}>
                  <Table>
                    <Table.Header>
                      <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
                      <Table.HeaderCell>
                        {translate('pim_common.code')} {translate('pim_common.required_label')}
                      </Table.HeaderCell>
                      <Table.HeaderCell />
                    </Table.Header>
                    <ManageOptionsBody>
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
                          onDelete={() => {
                            openDeleteOptionModal();
                            setIndexToRemove(getRealIndex(option));
                          }}
                          violations={violations[option.id]}
                          localeCode={currentLocaleCode}
                          onLabelEnter={isLastOption(option) ? () => newLabelInputRef.current?.focus() : undefined}
                          onCodeEnter={isLastOption(option) ? () => newCodeInputRef.current?.focus() : undefined}
                        />
                      ))}
                      <ManageOptionsRow
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
                    </ManageOptionsBody>
                  </Table>
                  {filteredOptions.length === 0 && searchValue !== '' && (
                    <CenteredHelper>
                      <AddingValueIllustration size={120} />
                      TODO Sorry, there are no options for your search!
                    </CenteredHelper>
                  )}
                  {filteredOptions.length === 0 && searchValue === '' && (
                    <CenteredHelper>
                      <AddingValueIllustration size={120} />
                      TODO Please add options
                    </CenteredHelper>
                  )}
                </TableContainer>
              </>
            )}
          </div>
          <Modal.TopRightButtons>
            <Button level='primary' onClick={handleConfirm} disabled={!canSave}>
              {translate('pim_common.confirm')}
            </Button>
          </Modal.TopRightButtons>
        </OptionsTwoColumnsLayout>
      </Modal>
      {isDeleteOptionModalOpen && typeof indexToRemove !== 'undefined' && options && (
        <DeleteOptionModal
          close={closeDeleteOptionModal}
          onDelete={handleDelete}
          optionCode={options[indexToRemove]?.code ?? ''}
        />
      )}
    </>
  );
};

export {ManageOptionsModal};
