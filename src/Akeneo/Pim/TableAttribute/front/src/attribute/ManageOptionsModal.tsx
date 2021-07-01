import React from 'react';
import {Button, Field, Modal, SectionTitle, Table, TextInput, uuid} from "akeneo-design-system";
import {getLabel, Locale, LocaleCode, useRouter, useTranslate, useUserContext} from "@akeneo-pim-community/shared";
import {ColumnDefinition, SelectOption} from "../models/TableConfiguration";
import {Attribute} from "../models/Attribute";
import {TwoColumnsLayout} from "./TwoColumnsLayout";
import {fetchActivatedLocales} from "../fetchers/LocaleFetcher";
import {FieldsList} from "../shared/FieldsList";
import styled from "styled-components";

const OptionsTwoColumnsLayout = styled(TwoColumnsLayout)`
  width: 1200px;
  height: calc(100vh - 150px);
`

type ManageOptionsModalProps = {
  onClose: () => void;
  attribute: Attribute;
  columnDefinition: ColumnDefinition;
};

type SelectOptionWithId = SelectOption & {
  id: string;
}


const ManageOptionsModal: React.FC<ManageOptionsModalProps> = ({
  onClose,
  attribute,
  columnDefinition,
}) => {
  const userContext = useUserContext();
  const router = useRouter();
  const translate = useTranslate();

  const [activedLocales, setActivatedLocales] = React.useState<Locale[]>();
  const [selectedOptionId, setSelectedOptionId] = React.useState<string | undefined>(undefined);
  const [options, setOptions] = React.useState<SelectOptionWithId[]>();
  const currentLocale = 'en_US';
  const columnLabel = getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code);

  React.useEffect(() => {
    fetchActivatedLocales(router).then((activeLocales: Locale[]) => setActivatedLocales(activeLocales));
  }, [router]);

  React.useEffect(() => {
    const options = [
      {id: uuid(), code: 'option1', labels: {'en_US': 'Option 1'}},
      {id: uuid(), code: 'option2', labels: {'en_US': 'Option 2'}},
      {id: uuid(), code: 'option3', labels: {'en_US': 'Option 3'}},
      {id: uuid(), code: 'option4', labels: {'en_US': 'Option 4'}},
      {id: uuid(), code: 'option5', labels: {'en_US': 'Option 5'}},
      {id: uuid(), code: '', labels: {}}
    ] as SelectOptionWithId[];
    setOptions(options);
    setSelectedOptionId(options[0].id);
  }, []);


  if (!activedLocales || typeof options === 'undefined') {
    return <div>TODO Loading</div>
  }

  const selectedOption = options.find(option => option.id === selectedOptionId);

  const handleLabelChange = (optionId: string, localeCode: LocaleCode, label: string) => {
    const index = options.findIndex(option => option.id === optionId);
    if (index >= 0) {
      const option = options[index];
      option.labels[localeCode] = label;
      options[index] = option;
      if (index === options.length - 1) {
        options.push({ id: uuid(), code: '', labels: {} });
      }
      setOptions([...options]);
    }
  }

  const handleCodeChange = (optionId: string, code: string) => {
    const index = options.findIndex(option => option.id === optionId);
    if (index >= 0) {
      options[index] = {...options[index], code};
      setOptions([...options]);
    }
  }

  const LabelTranslations = <>
    <SectionTitle title={translate('pim_common.label_translations')}>
      <SectionTitle.Title>{translate('pim_common.label_translations')}</SectionTitle.Title>
    </SectionTitle>
    {selectedOption && selectedOptionId &&
    <FieldsList>
      {activedLocales.map(locale => (
        <Field label={locale.label} key={locale.code} locale={locale.code}>
          <TextInput
            onChange={label => handleLabelChange(selectedOptionId, locale.code, label)}
            value={selectedOption.labels[locale.code] ?? ''}
          />
        </Field>
      ))}
    </FieldsList>
    }
  </>

  return <Modal closeTitle={translate('pim_common.close')} onClose={onClose}>
    <Modal.SectionTitle color="brand">
      {getLabel(attribute.labels, userContext.get('catalogLocale'), attribute.code)}&nbsp;/&nbsp;
      {columnLabel}</Modal.SectionTitle>
    <Modal.Title>TOTO Manage options</Modal.Title>
    <OptionsTwoColumnsLayout rightColumn={LabelTranslations}>
      <div>
        <SectionTitle title={columnLabel}>
          <SectionTitle.Title>{columnLabel}</SectionTitle.Title>
        </SectionTitle>
        <Table>
          <Table.Header>
            <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_common.code')} {translate('pim_common.required_label')}</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            {options.map((option, index) => <Table.Row
                key={option.id}
                isSelected={option.id === selectedOptionId}
                onClick={() => setSelectedOptionId(option.id)}
              >
                <Table.Cell>
                  <TextInput
                    onChange={label => handleLabelChange(option.id, currentLocale, label)}
                    value={option.labels[currentLocale] || ''}
                    placeholder={index === options.length - 1 ? 'Write your new option label' : ''}
                  />
                </Table.Cell>
                <Table.Cell>
                  <TextInput
                    onChange={code => handleCodeChange(option.id, code)}
                    value={option.code}
                  />
                </Table.Cell>
              </Table.Row>
            )}
          </Table.Body>
        </Table>
      </div>
    </OptionsTwoColumnsLayout>
    <Modal.TopRightButtons>
      <Button level="primary" onClick={onClose}>
        {translate('pim_common.save')}
      </Button>
    </Modal.TopRightButtons>
  </Modal>
}

export {ManageOptionsModal}
