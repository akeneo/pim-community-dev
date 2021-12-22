import React, {useState, useEffect, useCallback} from 'react';
import styled, {ThemeProvider} from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {HeaderCell, LabelCell, Row, Table} from 'akeneosharedcatalog/common/Table';
import {
  AkeneoThemedProps,
  arrayUnique,
  Button,
  Checkbox,
  CloseIcon,
  Field,
  Helper,
  NoResultsIllustration,
  pimTheme,
  Search,
  TagInput,
  SurveyIllustration,
  Key,
  useShortcut,
} from 'akeneo-design-system';

const MAX_RECIPIENT_COUNT = 500;

type Recipient = {
  email: string;
};

type ValidationError = {
  email?: string;
};

type ValidationErrors = {
  [index: number]: ValidationError;
};

type RecipientsProps = {
  recipients: Recipient[];
  validationErrors: ValidationErrors;
  onRecipientsChange: (updatedRecipients: Recipient[]) => void;
};

const emailRegex = /\S+@\S+\.\S+/;
const isValidEmail = (email: string) => {
  return emailRegex.test(email);
};

const Body = styled.div``;

const Form = styled.div`
  align-items: flex-end;
  display: flex;
  justify-content: center;
  padding: 30px 0;
  width: 100%;
`;

const LargeField = styled(Field)`
  width: 50%;
`;

const ErrorMessage = styled.span`
  color: ${({theme}: AkeneoThemedProps) => theme.color.red100};
  display: inline-block;
  font-size: 11px;
  font-style: normal;
  line-height: 13px;
  margin: 0 0 0 20px;
`;

const ActionCell = styled(LabelCell)`
  width: 50px !important;
  color: ${({theme}: AkeneoThemedProps) => theme.color.grey100};

  svg {
    margin-top: 6px;
  }
`;

const NoResults = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  color: ${({theme}: AkeneoThemedProps) => theme.color.grey140};
  line-height: 28px;
  margin-top: 40px;
`;

const RecipientCheckbox = styled(Checkbox)`
  position: absolute;
  right: 100%;
  opacity: ${({checked}) => (checked ? 1 : 0)};
  margin-right: 10px;
`;

const Cell = styled(LabelCell)`
  width: auto !important;
  position: relative;

  :hover {
    ${RecipientCheckbox} {
      opacity: 1;
    }
  }
`;

const Footer = styled.div`
  position: fixed;
  left: 80px;
  bottom: 0;
  width: 100%;
  height: 69px;
  border-top: 1px solid ${({theme}: AkeneoThemedProps) => theme.color.grey80};
  display: flex;
  align-items: center;
  padding: 20px;
  background: ${({theme}: AkeneoThemedProps) => theme.color.white};
`;

const ItemsCount = styled.div`
  width: 120px;
  text-transform: uppercase;
  margin: 0 20px;
`;

const Container = (props: RecipientsProps) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <Recipients {...props} />
      </ThemeProvider>
    </DependenciesProvider>
  );
};

const Recipients = ({recipients, validationErrors, onRecipientsChange}: RecipientsProps) => {
  const translate = useTranslate();
  const [currentRecipients, setCurrentRecipients] = useState<Recipient[]>(recipients);
  const [recipientsToAdd, setRecipientsToAdd] = useState<Recipient[]>([]);
  const [searchValue, setSearchValue] = useState<string>('');
  const [recipientSelection, setRecipientSelection] = useState<Recipient[]>([]);

  const emailToAddContainInvalid = recipientsToAdd.some(recipientToAdd => !isValidEmail(recipientToAdd.email));
  const currentRecipientEmail = currentRecipients.map(currentRecipient => currentRecipient.email);
  const emailToAddIsDuplicated = recipientsToAdd.some(recipientToAdd =>
    currentRecipientEmail.includes(recipientToAdd.email)
  );
  const maxRecipientLimitReached = MAX_RECIPIENT_COUNT <= currentRecipients.length;

  const handleRecipientsInputChange = useCallback(
    (addresses: string[]) => {
      setRecipientsToAdd(addresses.map(address => ({email: address})));
    },
    [recipientsToAdd]
  );

  const handleAddRecipients = useCallback(() => {
    if (0 === recipientsToAdd.length || emailToAddContainInvalid) return;

    const newRecipients = arrayUnique<Recipient>(
      [...currentRecipients, ...recipientsToAdd],
      (first, second) => first.email === second.email
    );
    setCurrentRecipients(newRecipients.slice(0, MAX_RECIPIENT_COUNT));
    setRecipientsToAdd(newRecipients.slice(MAX_RECIPIENT_COUNT));
  }, [currentRecipients, recipientsToAdd, emailToAddContainInvalid]);

  useShortcut(Key.Enter, handleAddRecipients);
  useShortcut(Key.NumpadEnter, handleAddRecipients);

  useEffect(() => {
    onRecipientsChange(currentRecipients);
  }, [currentRecipients]);

  const filteredRecipients = currentRecipients.filter(
    recipient => -1 !== recipient.email.toLowerCase().indexOf(searchValue.toLowerCase())
  );

  const toggleRecipient = useCallback(
    (recipient: Recipient) =>
      setRecipientSelection(recipientSelection =>
        recipientSelection.includes(recipient)
          ? recipientSelection.filter(item => item !== recipient)
          : [...recipientSelection, recipient]
      ),
    [recipientSelection]
  );

  return (
    <Body>
      <Helper level="info">{translate('shared_catalog.recipients.helper')}</Helper>
      {'string' === typeof validationErrors && <Helper level="error">{translate(validationErrors)}</Helper>}
      <Form>
        <LargeField
          label={translate('shared_catalog.recipients.add')}
          actions={
            <Button
              level="tertiary"
              ghost={true}
              onClick={handleAddRecipients}
              size="small"
              disabled={0 === recipientsToAdd.length || emailToAddContainInvalid || maxRecipientLimitReached}
            >
              {translate('pim_common.add')}
            </Button>
          }
        >
          <TagInput
            disabled={maxRecipientLimitReached}
            onChange={handleRecipientsInputChange}
            placeholder={translate('shared_catalog.recipients.placeholder')}
            value={recipientsToAdd.map(recipient => recipient.email)}
          />
          {maxRecipientLimitReached && (
            <Helper inline={true} level="info">
              {translate('shared_catalog.recipients.max_limit_reached')}
            </Helper>
          )}
          {emailToAddContainInvalid && (
            <Helper inline={true} level="error">
              {translate('shared_catalog.recipients.invalid_email')}
            </Helper>
          )}
          {emailToAddIsDuplicated && (
            <Helper inline={true} level="warning">
              {translate('shared_catalog.recipients.duplicate_email')}
            </Helper>
          )}
        </LargeField>
      </Form>
      <Search
        title={translate('pim_common.search')}
        placeholder={translate('pim_common.search')}
        searchValue={searchValue}
        onSearchChange={setSearchValue}
      >
        <Search.ResultCount>
          {translate('pim_common.result_count', {itemsCount: filteredRecipients.length}, filteredRecipients.length)}
        </Search.ResultCount>
      </Search>
      <Table title="recipients">
        <thead>
          <Row>
            <HeaderCell>{translate('shared_catalog.recipients.email')}</HeaderCell>
            <HeaderCell />
          </Row>
        </thead>
        <tbody>
          {filteredRecipients.map((recipient, index) => {
            const handleCheckboxChange = () => toggleRecipient(recipient);
            const handleRecipientRemove = () => {
              setCurrentRecipients(currentRecipients =>
                currentRecipients.filter(currentRecipient => currentRecipient !== recipient)
              );
              setRecipientSelection(recipientSelection => recipientSelection.filter(item => item !== recipient));
            };

            return (
              <Row key={`${recipient.email}-${index}`}>
                <Cell onClick={handleCheckboxChange}>
                  <RecipientCheckbox checked={recipientSelection.includes(recipient)} onChange={handleCheckboxChange} />
                  {recipient.email}
                  {validationErrors[index] && <ErrorMessage>{validationErrors[index].email}</ErrorMessage>}
                </Cell>
                <ActionCell>
                  <CloseIcon onClick={handleRecipientRemove} size={20} title={translate('pim_common.remove')} />
                </ActionCell>
              </Row>
            );
          })}
        </tbody>
      </Table>
      {0 === currentRecipients.length ? (
        <NoResults>
          <SurveyIllustration size={80} />
          {translate('shared_catalog.recipients.no_data')}
        </NoResults>
      ) : (
        '' !== searchValue &&
        0 === filteredRecipients.length && (
          <NoResults>
            <NoResultsIllustration size={80} />
            {translate('shared_catalog.recipients.no_result')}
          </NoResults>
        )
      )}
      {0 < recipientSelection.length && (
        <Footer>
          <Checkbox
            title={translate('pim_common.all')}
            checked={currentRecipients.every(recipient => recipientSelection.includes(recipient))}
            onChange={checked => setRecipientSelection(true === checked ? currentRecipients : [])}
          />
          <ItemsCount>
            {translate(
              'pim_common.items_selected',
              {itemsCount: recipientSelection.length.toString()},
              recipientSelection.length
            )}
          </ItemsCount>
          <Button
            level="danger"
            onClick={() => {
              setCurrentRecipients(currentRecipients =>
                currentRecipients.filter(currentRecipient => !recipientSelection.includes(currentRecipient))
              );
              setRecipientSelection([]);
            }}
          >
            {translate('pim_common.delete')}
          </Button>
        </Footer>
      )}
    </Body>
  );
};

export {Container as default, Recipients, MAX_RECIPIENT_COUNT};
