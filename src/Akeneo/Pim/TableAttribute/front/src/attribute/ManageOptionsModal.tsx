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
} from 'akeneo-design-system';
import {getLabel, Locale, LocaleCode, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {SelectColumnDefinition, SelectOption} from '../models/TableConfiguration';
import {Attribute} from '../models/Attribute';
import {TwoColumnsLayout} from './TwoColumnsLayout';
import {FieldsList} from '../shared/FieldsList';
import styled from 'styled-components';
import {fetchSelectOptions} from '../fetchers/SelectOptionsFetcher';
import {getActivatedLocales} from '../repositories/Locale';
import {Child} from './Child';

const TableContainer = styled.div`
  height: calc(100vh - 300px);
  overflow: auto;
`;

const OptionsTwoColumnsLayout = styled(TwoColumnsLayout)`
  width: 1200px;
  height: calc(100vh - 150px);
`;

const ManageOptionsBody = styled(Table.Body)`
  & > tr:last-child {
    position: sticky;
    bottom: 0;
    background-color: ${getColor('white')};
    z-index: 1;
  }
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

  const lastCodeInputRef = React.useRef<HTMLInputElement>();
  const lastLabelInputRef = React.useRef<HTMLInputElement>();
  const newCodeInputRef = React.useRef<HTMLInputElement>();
  const newLabelInputRef = React.useRef<HTMLInputElement>();
  const tableContainerRef = React.useRef();

  const currentLocaleCode = 'en_US';
  const columnLabel = getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code);
  const canSave = Object.keys(violations).length === 0;
  const currentOption =
    typeof selectedOptionIndex === 'undefined' || !options ? undefined : options[selectedOptionIndex];

  React.useEffect(() => {
    const initializeOptions = (newOptions: SelectOption[]) => {
      const optionsWithId = newOptions.map(option => {
        return {...option, id: uuid(), isNew: false};
      });
      setOptions(optionsWithId);
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
    if (options && options.length > 0) {
      const option = options[options.length - 1];
      if (typeof option.labels[currentLocaleCode] !== 'undefined') {
        lastLabelInputRef.current?.focus();
      } else {
        lastCodeInputRef.current?.focus();
      }
    }
    if (tableContainerRef.current) {
      tableContainerRef.current.scrollTop = tableContainerRef.current.scrollHeight;
    }
  }, [options?.length]);

  React.useEffect(() => {
    getActivatedLocales(router).then((activeLocales: Locale[]) => setActivatedLocales(activeLocales));
  }, [router]);

  const setOptionsAndValidate = (newOptions: SelectOptionWithId[]) => {
    setOptions([...newOptions]);

    const duplicates = newOptions
      .map(optionA => optionA.code)
      .filter(optionCode => {
        return newOptions.filter(optionB => optionB.code === optionCode).length > 1;
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
        if (option.code !== '' && duplicates.includes(option.code)) {
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
    const newOptions = [...(options || []), {...option, id: uuid()}];
    setOptionsAndValidate(newOptions);
    setPage(Math.floor((newOptions.length - 1) / OPTIONS_PER_PAGE) + 1);
    option.code = '';
    option.labels = {};
    if (newCodeInputRef.current) newCodeInputRef.current.value = '';
    if (newLabelInputRef.current) newLabelInputRef.current.value = '';
  };

  const handleDelete = (index: number) => {
    const tempValue = [...(options || [])];
    tempValue.splice(index, 1);
    setOptionsAndValidate(tempValue);
  };

  const getRealIndex = React.useCallback((index: number) => index + (page - 1) * OPTIONS_PER_PAGE, [page]);
  const isLastOption = React.useCallback((index: number) => options && getRealIndex(index) === options?.length - 1, [
    options?.length,
  ]);

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
          {options && (
            <Pagination
              currentPage={page}
              totalItems={options?.length ?? 0}
              itemsPerPage={OPTIONS_PER_PAGE}
              followPage={setPage}
            />
          )}
          {!options && <LoaderIcon />}
          {options && (
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
                  {options.slice((page - 1) * OPTIONS_PER_PAGE, page * OPTIONS_PER_PAGE).map((option, index) => (
                    <Child
                      codeInputRef={isLastOption(index) ? lastCodeInputRef : undefined}
                      labelInputRef={isLastOption(index) ? lastLabelInputRef : undefined}
                      isSelected={selectedOptionIndex === getRealIndex(index)}
                      onSelect={() => setSelectedOptionIndex(getRealIndex(index))}
                      data-testid={`row-${getRealIndex(index)}`}
                      onChange={(option: SelectOptionWithId) => handleOptionChange(getRealIndex(index), option)}
                      key={option.id}
                      option={option}
                      onDelete={() => handleDelete(getRealIndex(index))}
                      violations={violations[option.id]}
                    />
                  ))}
                  <Child
                    codeInputRef={newCodeInputRef}
                    labelInputRef={newLabelInputRef}
                    isSelected={selectedOptionIndex === -1}
                    onSelect={() => setSelectedOptionIndex(-1)}
                    data-testid={`row-new`}
                    onChange={(option: SelectOptionWithId) => handleAddOption(option)}
                    option={emptySelectOption}
                    violations={[]}
                    labelPlaceholder={translate('pim_table_attribute.form.attribute.new_option_placeholder')}
                  />
                </ManageOptionsBody>
              </Table>
            </TableContainer>
          )}
        </div>
        <Modal.TopRightButtons>
          <Button level='primary' onClick={handleConfirm} disabled={!canSave}>
            {translate('pim_common.confirm')}
          </Button>
        </Modal.TopRightButtons>
      </OptionsTwoColumnsLayout>
    </Modal>
  );
};

export {ManageOptionsModal};
