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
  const [activatedLocales, setActivatedLocales] = React.useState<Locale[]>();
  const [selectedOptionId, setSelectedOptionId] = React.useState<string | undefined>(undefined);
  const [options, setOptions] = React.useState<SelectOptionWithId[]>();
  const [autoCompleteCode, setAutoCompleteCode] = React.useState<boolean>(false);
  const [filteredOptionIds, setFilteredOptionIds] = React.useState<string[]>([]);
  const [scrollToTheEnd, setScrollToTheEnd] = React.useState<boolean>(false);
  const [numberOfItemsToDisplay, setNumberOfItemsToDisplay] = React.useState<number>(0);
  const currentLocale = 'en_US';
  const columnLabel = getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code);

  React.useEffect(() => {
    getActivatedLocales(router).then((activeLocales: Locale[]) => setActivatedLocales(activeLocales));
  }, [router]);

  const setOptionsWithCheck = (options: SelectOptionWithId[]) => {
    const optionCodes = options.map(option => option.code);
    const duplicates = optionCodes.filter(optionCode => {
      return options.filter(o => o.code === optionCode).length > 1;
    });

    setOptions(
      options.map((option, index) => {
        const violations = [];
        if (index !== options.length - 1) {
          if (option.code === '') {
            violations.push(translate('pim_table_attribute.validations.column_code_must_be_filled'));
          }
          if (option.code !== '' && !/^[a-zA-Z0-9_]+$/.exec(option.code)) {
            violations.push(translate('pim_table_attribute.validations.invalid_code'));
          }
          if (option.code !== '' && duplicates.includes(option.code)) {
            violations.push(translate('pim_table_attribute.validations.duplicated_select_code'));
          }
        }
        return {...option, violations};
      })
    );
  };

  const initializeOptions = (options: SelectOption[]) => {
    const optionsWithId = options.map(option => {
      return {...option, id: uuid()};
    });
    const lastId = uuid();
    optionsWithId.push({id: lastId, code: '', labels: {}});
    setOptionsWithCheck(optionsWithId);
    setSelectedOptionId(optionsWithId[0]?.id);
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

    const lastOption = options[options.length - 1];
    const hiddenOptionIds = options
      .filter(option => !filteredOptionIds.includes(option.id) && option.id !== lastOption?.id)
      .map(option => option.id);
    const filteredOptionIdsTmp = [
      ...filteredOptionIds.filter(optionId => optionId !== lastOption?.id),
      ...hiddenOptionIds.slice(0, BATCH_SIZE),
    ];
    if (lastOption && !filteredOptionIdsTmp.includes(lastOption.id)) {
      filteredOptionIdsTmp.push(lastOption?.id);
    }
    setFilteredOptionIds(filteredOptionIdsTmp);
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

  const selectedOption = options ? options.find(option => option.id === selectedOptionId) : undefined;

  const handleLabelChange = (optionId: string, localeCode: LocaleCode, label: string) => {
    if (options) {
      const index = options.findIndex(option => option.id === optionId);
      if (index >= 0) {
        const option = options[index];
        option.labels[localeCode] = label;
        if (autoCompleteCode) {
          option.code = label.replace(/[^a-zA-Z0-9_]/gi, '_').substring(0, 100);
        }
        options[index] = option;
        if (filteredOptionIds[filteredOptionIds.length - 1] === optionId) {
          const newId = uuid();
          options.push({id: newId, code: '', labels: {}});
          setFilteredOptionIds([...filteredOptionIds, newId]);
          setScrollToTheEnd(true);
        }
        setOptionsWithCheck(options);
      }
    }
  };

  const handleCodeChange = (optionId: string, code: string) => {
    if (options) {
      const index = options.findIndex(option => option.id === optionId);
      if (index >= 0) {
        options[index] = {...options[index], code};
        if (filteredOptionIds[filteredOptionIds.length - 1] === optionId) {
          const newId = uuid();
          options.push({id: newId, code: '', labels: {}});
          setFilteredOptionIds([...filteredOptionIds, newId]);
          setScrollToTheEnd(true);
        }
        setOptionsWithCheck(options);
      }
    }
  };

  const handleRemove = (optionId: string) => {
    if (options) {
      if (optionId === selectedOptionId) {
        const index = options.findIndex(option => option.id === optionId);
        setSelectedOptionId(options[index + 1].id);
      }
      setOptionsWithCheck(options.filter(option => option.id !== optionId));
    }
  };

  const handleFocus = (optionId: string) => {
    if (options) {
      const option = options.find(option => option.id === optionId) as SelectOptionWithId;
      setAutoCompleteCode(typeof option.labels[currentLocale] === 'undefined' || option.labels[currentLocale] === '');
    }
  };

  const handleBlur = () => {
    setAutoCompleteCode(false);
  };

  const handleSave = () => {
    if (options) {
      onChange(
        options.slice(0, -1).map(option => {
          const {id, violations, ...rest} = option;
          return rest;
        })
      );
    }
    onClose();
  };

  const canSave = options && options.every(option => option.violations?.length === 0);

  const LabelTranslations = (
    <>
      <SectionTitle title={translate('pim_common.label_translations')}>
        <SectionTitle.Title>{translate('pim_common.label_translations')}</SectionTitle.Title>
      </SectionTitle>
      {selectedOption && selectedOptionId && (
        <FieldsList>
          {!activatedLocales && <LoaderIcon />}
          {activatedLocales &&
            activatedLocales.map(locale => (
              <Field label={locale.label} key={locale.code} locale={locale.code}>
                <TextInput
                  onChange={label => handleLabelChange(selectedOptionId, locale.code, label)}
                  value={selectedOption.labels[locale.code] ?? ''}
                  maxLength={255}
                />
              </Field>
            ))}
        </FieldsList>
      )}
    </>
  );

  const optionsToDisplay: SelectOptionWithId[] | null = options
    ? filteredOptionIds.map(optionId => options.find(option => optionId === option.id))
    : null;

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
                {options &&
                  optionsToDisplay &&
                  optionsToDisplay.map((option: SelectOptionWithId, index) => (
                    <ManageOptionsRow
                      key={option.id}
                      isSelected={option.id === selectedOptionId}
                      onClick={() => setSelectedOptionId(option.id)}
                      isLastRow={index === optionsToDisplay.length - 1}>
                      <ManageOptionCell>
                        <CellFieldContainer>
                          <TextInput
                            onChange={label => handleLabelChange(option.id, currentLocale, label)}
                            value={option.labels[currentLocale] || ''}
                            placeholder={
                              index === optionsToDisplay.length - 1
                                ? translate('pim_table_attribute.form.attribute.new_option_placeholder')
                                : ''
                            }
                            onFocus={() => handleFocus(option.id)}
                            onBlur={handleBlur}
                            maxLength={255}
                            data-testid={`label-${index}`}
                          />
                        </CellFieldContainer>
                      </ManageOptionCell>
                      <ManageOptionCell>
                        <CellFieldContainer>
                          <TextInput
                            onChange={code => handleCodeChange(option.id, code)}
                            value={option.code}
                            maxLength={100}
                            data-testid={`code-${index}`}
                          />
                          {(option.violations ?? []).map((violation, i) => (
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
