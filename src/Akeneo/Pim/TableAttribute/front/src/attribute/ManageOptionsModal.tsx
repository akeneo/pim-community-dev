import React from 'react';
import {
  Button,
  Field,
  IconButton,
  Modal,
  SectionTitle,
  Table,
  TextInput,
  uuid,
  CloseIcon,
  LoaderIcon,
  Helper,
  AkeneoThemedProps,
  getColor,
} from 'akeneo-design-system';
import {getLabel, Locale, LocaleCode, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {SelectColumnDefinition, SelectOption} from '../models/TableConfiguration';
import {Attribute} from '../models/Attribute';
import {TwoColumnsLayout} from './TwoColumnsLayout';
import {FieldsList} from '../shared/FieldsList';
import styled, {css} from 'styled-components';
import {fetchSelectOptions} from '../fetchers/SelectOptionsFetcher';
import {getActivatedLocales} from '../repositories/Locale';

const TableContainer = styled.div`
  height: calc(100vh - 200px);
  overflow: auto;
`;

const OptionsTwoColumnsLayout = styled(TwoColumnsLayout)`
  width: 1200px;
  height: calc(100vh - 150px);
`;

const ManageOptionCell = styled(Table.Cell)`
  vertical-align: top;
`;

const CellFieldContainer = styled.div`
  flex-grow: 1;
`;

const ManageOptionsRow = styled(Table.Row)<{isLastRow: boolean} & AkeneoThemedProps>`
  ${({isLastRow}) =>
    isLastRow &&
    css`
      position: sticky;
      bottom: 0;
      background-color: ${getColor('white')};
    `}
`;

type ManageOptionsModalProps = {
  onClose: () => void;
  attribute: Attribute;
  columnDefinition: SelectColumnDefinition;
  onChange: (options: SelectOption[]) => void;
};

type SelectOptionWithId = SelectOption & {
  id: string;
  violations?: string[];
};

const BATCH_SIZE = 100;

const ManageOptionsModal: React.FC<ManageOptionsModalProps> = ({onClose, attribute, columnDefinition, onChange}) => {
  const userContext = useUserContext();
  const router = useRouter();
  const translate = useTranslate();

  const tableContainerRef = React.useRef();
  const inputCodeRef = React.useRef();
  const [activatedLocales, setActivatedLocales] = React.useState<Locale[]>();
  const [selectedOption, setSelectedOption] = React.useState<SelectOptionWithId | undefined>(undefined);
  const [options, setOptions] = React.useState<SelectOptionWithId[]>();
  const [optionsToDisplay, setOptionsToDisplay] = React.useState<SelectOptionWithId[]>();
  const [autoCompleteCode, setAutoCompleteCode] = React.useState<boolean>(false);
  const [violations, setViolations] = React.useState<{[key: string]: string[]}>({});
  const [scrollToTheEnd, setScrollToTheEnd] = React.useState<boolean>(false);
  const [numberOfItemsToDisplay, setNumberOfItemsToDisplay] = React.useState<number>(0);
  const currentLocale = 'en_US';
  const columnLabel = getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code);

  React.useEffect(() => {
    getActivatedLocales(router).then((activeLocales: Locale[]) => setActivatedLocales(activeLocales));
  }, [router]);

  const computeOptionsToDisplay = () => {
    if (!options) {
      return;
    }

    const newOptionsToDisplay = options.slice(0, numberOfItemsToDisplay);
    newOptionsToDisplay.push({id: uuid(), code: '', labels: {}});
    setOptionsToDisplay(newOptionsToDisplay);
  };

  const setOptionsAndValidation = (options: SelectOptionWithId[]) => {
    const optionCodes = options.map(option => option.code);
    const duplicates = optionCodes.filter(optionCode => {
      return options.filter(o => o.code === optionCode).length > 1;
    });

    setOptions(options);
    if (selectedOption) {
      const found = options.find(option => option.id === selectedOption.id);
      setSelectedOption(found ? {...found} : undefined);
    }

    const newViolations = {};
    options.forEach(option => {
      const violationsForOption = [];
      if (option.code === '') {
        violationsForOption.push(translate('pim_table_attribute.validations.column_code_must_be_filled'));
      }
      if (option.code !== '' && !/^[a-zA-Z0-9_]+$/.exec(option.code)) {
        violationsForOption.push(translate('pim_table_attribute.validations.invalid_code'));
      }
      if (option.code !== '' && duplicates.includes(option.code)) {
        violationsForOption.push(translate('pim_table_attribute.validations.duplicated_select_code'));
      }
      if (violationsForOption.length > 0) {
        newViolations[option.id] = violationsForOption;
      }
    });
    if (JSON.stringify(newViolations) !== JSON.stringify(violations)) {
      setViolations(newViolations);
    }
  };

  const initializeOptions = (options: SelectOption[]) => {
    const optionsWithId = options.map(option => {
      return {...option, id: uuid()};
    });
    setOptionsAndValidation(optionsWithId);
    setSelectedOption(optionsWithId[0] ?? undefined);
  };

  React.useEffect(() => {
    if (typeof columnDefinition.options === 'undefined') {
      fetchSelectOptions(router, attribute.code, columnDefinition.code).then(fetchOptions => {
        if (typeof fetchOptions === 'undefined') {
          initializeOptions([]);
        } else {
          initializeOptions(fetchOptions);
        }
        setNumberOfItemsToDisplay(BATCH_SIZE);
      });
    } else {
      initializeOptions(columnDefinition.options);
      setNumberOfItemsToDisplay(BATCH_SIZE);
    }
  }, []);

  React.useEffect(() => {
    if (numberOfItemsToDisplay === 0 || !options) {
      return;
    }
    computeOptionsToDisplay();
  }, [numberOfItemsToDisplay]);

  React.useEffect(() => {
    if (tableContainerRef?.current) {
      tableContainerRef.current.onscroll = () => {
        if (
          tableContainerRef.current.scrollTop + tableContainerRef.current.offsetHeight >=
            tableContainerRef.current.scrollHeight &&
          numberOfItemsToDisplay < options.length
        ) {
          setNumberOfItemsToDisplay(numberOfItemsToDisplay + BATCH_SIZE);
        }
      };
    }
  }, [tableContainerRef, numberOfItemsToDisplay]);

  React.useEffect(() => {
    if (scrollToTheEnd && tableContainerRef.current) {
      window.setTimeout(() => {
        tableContainerRef.current.scrollTop = tableContainerRef.current.scrollHeight;
      }, 0);
      setScrollToTheEnd(false);
    }
  }, [scrollToTheEnd]);

  const handleLabelChange = (optionId: string, localeCode: LocaleCode, label: string) => {
    if (options) {
      const index = options.findIndex(option => option.id === optionId);
      const option = index >= 0 ? options[index] : {id: optionId, code: '', labels: {}};

      option.labels[localeCode] = label;
      if (autoCompleteCode) {
        option.code = label.replace(/[^a-zA-Z0-9_]/gi, '_').substring(0, 100);
        if (inputCodeRef && inputCodeRef.current) {
          inputCodeRef.current.value = option.code;
        }
      }
      if (index >= 0) {
        options[index] = option;
      } else {
        options.push(option);
        if (optionsToDisplay) {
          optionsToDisplay.push({id: uuid(), code: '', labels: {}});
          setOptionsToDisplay([...optionsToDisplay]);
          setAutoCompleteCode(true);
        }
      }
      setOptionsAndValidation(options);
    }
  };

  const handleCodeChange = (optionId: string, code: string) => {
    if (options) {
      const index = options.findIndex(option => option.id === optionId);
      const option = index >= 0 ? options[index] : {id: optionId, code: '', labels: {}};
      option.code = code;

      if (index >= 0) {
        options[index] = option;
      } else {
        options.push(option);
        if (optionsToDisplay) {
          optionsToDisplay.push({id: uuid(), code: '', labels: {}});
          setOptionsToDisplay([...optionsToDisplay]);
        }
      }
      setOptionsAndValidation(options);
    }
  };

  const handleRemove = (optionId: string) => {
    if (options) {
      if (optionId === selectedOption?.id) {
        const index = options.findIndex(option => option.id === optionId);
        setSelectedOption(options[index === options.length - 1 ? options.length - 2 : index + 1]);
      }
      setOptionsAndValidation(options.filter(option => option.id !== optionId));
    }
  };

  const handleFocus = (optionId: string, enableAutoCompleteCode: boolean) => {
    setSelectedOption(optionsToDisplay?.find(option => option.id === optionId));
    if (enableAutoCompleteCode && optionsToDisplay) {
      let option = options?.find(option => option.id === optionId) as SelectOptionWithId;
      if (option === undefined) {
        option = optionsToDisplay?.find(option => option.id === optionId) as SelectOptionWithId;
      }
      setAutoCompleteCode(typeof option.code === 'undefined' || option.code === '');
    }
  };

  const handleBlur = () => {
    setAutoCompleteCode(false);
  };

  const handleSave = () => {
    if (options) {
      onChange(
        options.slice(0, -1).map(option => {
          const {id, ...rest} = option;
          return rest;
        })
      );
    }
    onClose();
  };

  const canSave = Object.keys(violations).length === 0;

  const LabelTranslations = (
    <>
      <SectionTitle title={translate('pim_common.label_translations')}>
        <SectionTitle.Title>{translate('pim_common.label_translations')}</SectionTitle.Title>
      </SectionTitle>
      {selectedOption && (
        <FieldsList>
          {!activatedLocales && <LoaderIcon />}
          {activatedLocales &&
            activatedLocales.map(locale => (
              <Field label={locale.label} key={locale.code} locale={locale.code}>
                <TextInput
                  onChange={label => handleLabelChange(selectedOption.id, locale.code, label)}
                  value={selectedOption.labels[locale.code] ?? ''}
                  maxLength={255}
                />
              </Field>
            ))}
        </FieldsList>
      )}
    </>
  );

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onClose}>
      <Modal.SectionTitle color='brand'>
        {getLabel(attribute.labels, userContext.get('catalogLocale'), attribute.code)}&nbsp;/&nbsp;
        {columnLabel}
      </Modal.SectionTitle>
      <Modal.Title>{translate('pim_table_attribute.form.attribute.manage_options')}</Modal.Title>
      <OptionsTwoColumnsLayout rightColumn={LabelTranslations}>
        <div>
          <SectionTitle title={columnLabel}>
            <SectionTitle.Title>{columnLabel}</SectionTitle.Title>
          </SectionTitle>
          {!options && <LoaderIcon />}
          <TableContainer ref={tableContainerRef}>
            <Table>
              <Table.Header>
                <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
                <Table.HeaderCell>
                  {translate('pim_common.code')} {translate('pim_common.required_label')}
                </Table.HeaderCell>
                <Table.HeaderCell />
              </Table.Header>
              <Table.Body>
                {optionsToDisplay &&
                  optionsToDisplay.map((option: SelectOptionWithId, index) => (
                    <ManageOptionsRow
                      key={option.id}
                      isSelected={option.id === selectedOption?.id}
                      isLastRow={index === optionsToDisplay.length - 1}>
                      <ManageOptionCell>
                        <CellFieldContainer>
                          <TextInput
                            onChange={label => handleLabelChange(option.id, currentLocale, label)}
                            defaultValue={option.labels[currentLocale] || ''}
                            placeholder={
                              index === optionsToDisplay.length - 1
                                ? translate('pim_table_attribute.form.attribute.new_option_placeholder')
                                : ''
                            }
                            onFocus={() => handleFocus(option.id, true)}
                            onBlur={handleBlur}
                            maxLength={255}
                            data-testid={`label-${index}`}
                          />
                        </CellFieldContainer>
                      </ManageOptionCell>
                      <ManageOptionCell>
                        <CellFieldContainer>
                          <TextInput
                            ref={selectedOption?.id === option.id ? inputCodeRef : null}
                            onChange={code => handleCodeChange(option.id, code)}
                            defaultValue={option.code}
                            maxLength={100}
                            onFocus={() => handleFocus(option.id, false)}
                            data-testid={`code-${index}`}
                          />
                          {violations[option.id] &&
                            violations[option.id].map((violation, i) => (
                              <Helper key={i} level='error' inline>
                                {violation}
                              </Helper>
                            ))}
                        </CellFieldContainer>
                      </ManageOptionCell>
                      <Table.ActionCell>
                        {index !== optionsToDisplay.length - 1 && (
                          <IconButton
                            ghost='borderless'
                            level='tertiary'
                            icon={<CloseIcon />}
                            title={translate('pim_common.remove')}
                            onClick={() => handleRemove(option.id)}
                          />
                        )}
                      </Table.ActionCell>
                    </ManageOptionsRow>
                  ))}
              </Table.Body>
            </Table>
          </TableContainer>
        </div>
      </OptionsTwoColumnsLayout>
      <Modal.TopRightButtons>
        <Button level='primary' onClick={handleSave} disabled={!canSave}>
          {translate('pim_common.confirm')}
        </Button>
      </Modal.TopRightButtons>
    </Modal>
  );
};

export {ManageOptionsModal};
