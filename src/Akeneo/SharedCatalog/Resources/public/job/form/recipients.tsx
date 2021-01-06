import React, {useState, useEffect, useRef, useCallback, ChangeEvent, SyntheticEvent} from 'react';
import styled, {ThemeProvider} from 'styled-components';
import {SearchBar} from '@akeneo-pim-community/shared';
import {DependenciesProvider, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {HeaderCell, LabelCell, Row, Table} from 'akeneosharedcatalog/common/Table';
import {
  AkeneoThemedProps,
  Button,
  Checkbox,
  CloseIcon,
  Helper,
  NoResultsIllustration,
  pimTheme,
  SurveyIllustration,
  Key,
  useAutoFocus,
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

const InputContainer = styled.div`
  display: flex;
  flex-direction: column;
`;

const Input = styled.textarea<{isInvalid: boolean}>`
  border-radius: 2px;
  border: 1px solid;
  border-color: ${({theme, isInvalid}: AkeneoThemedProps & {isInvalid: boolean}) =>
    isInvalid ? theme.color.red100 : theme.color.grey80};
  color: ${({theme}: AkeneoThemedProps & {isInvalid: boolean}) => theme.color.grey140};
  height: auto;
  line-height: 15px;
  min-height: 40px;
  max-height: 150px;
  margin-right: 10px;
  padding: 10px 10px 0 10px;
  width: 450px;
  z-index: 1;
  resize: none;

  :disabled {
    color: ${({theme}: AkeneoThemedProps & {isInvalid: boolean}) => theme.color.grey60};
    background-color: ${({theme}: AkeneoThemedProps & {isInvalid: boolean}) => theme.color.grey60};
    background-image: url('/bundles/pimui/images/icon-lock2.svg');
    background-size: 18px;
    background-position: 98% center;
    background-repeat: no-repeat;
  }
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

const InputWithButton = styled.div`
  display: flex;
  align-items: center;
  margin-top: 8px;
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
  const [recipientToAdd, setRecipientToAdd] = useState<string>('');
  const [emailIsValid, setEmailIsValid] = useState<boolean>(true);
  const [emailIsDuplicated, setEmailIsDuplicated] = useState<boolean>(false);
  const [searchValue, setSearchValue] = useState<string>('');
  const [recipientSelection, setRecipientSelection] = useState<Recipient[]>([]);
  const inputRef = useRef<null | HTMLTextAreaElement>(null);

  const handleAddNewRecipient = useCallback(
    (event: SyntheticEvent) => {
      event.preventDefault();

      if (emailIsDuplicated || !emailIsValid || '' === recipientToAdd) return;

      const addresses = recipientToAdd.split(/[\n\s,;]+/);

      if (1 < addresses.length) {
        const currentAddresses = currentRecipients.map(recipient => recipient.email);
        const newRecipients = addresses
          .filter(
            (recipient, index) =>
              isValidEmail(recipient) && !currentAddresses.includes(recipient) && index === addresses.indexOf(recipient)
          )
          .map(recipient => ({email: recipient}));
        setRecipientToAdd('');
        setCurrentRecipients(currentRecipients => [
          ...currentRecipients,
          ...newRecipients.slice(0, MAX_RECIPIENT_COUNT - currentAddresses.length),
        ]);
      } else {
        if (isValidEmail(addresses[0])) {
          setRecipientToAdd('');
          setCurrentRecipients(currentRecipients => [...currentRecipients, {email: addresses[0]}]);
        } else {
          setEmailIsValid(false);
        }
      }
    },
    [recipientToAdd, setEmailIsValid, emailIsDuplicated, currentRecipients]
  );

  useAutoFocus(inputRef);
  useShortcut(Key.Enter, handleAddNewRecipient);
  useShortcut(Key.NumpadEnter, handleAddNewRecipient);

  useEffect(() => {
    setEmailIsDuplicated(currentRecipients.map(recipient => recipient.email).includes(recipientToAdd));

    // Adapt height to content on each input change
    if (null !== inputRef.current) {
      inputRef.current.style.height = 'auto';
      inputRef.current.style.height = `${inputRef.current.scrollHeight}px`;
    }
  }, [recipientToAdd, currentRecipients]);

  useEffect(() => {
    onRecipientsChange(currentRecipients);
  }, [currentRecipients]);

  const filteredRecipients = currentRecipients.filter(
    recipient => -1 !== recipient.email.toLowerCase().indexOf(searchValue.toLowerCase())
  );

  const maxRecipientLimitReached = MAX_RECIPIENT_COUNT <= currentRecipients.length;

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
        <InputContainer>
          {translate('shared_catalog.recipients.add')}
          <InputWithButton>
            <Input
              ref={inputRef}
              placeholder={translate('shared_catalog.recipients.placeholder')}
              value={recipientToAdd}
              disabled={maxRecipientLimitReached}
              isInvalid={emailIsDuplicated || !emailIsValid}
              onChange={(event: ChangeEvent<HTMLTextAreaElement>) => {
                setEmailIsValid(true);
                setRecipientToAdd(event.currentTarget.value);
              }}
            />
            <Button
              level="tertiary"
              onClick={handleAddNewRecipient}
              disabled={emailIsDuplicated || '' === recipientToAdd || maxRecipientLimitReached}
              ghost={true}
            >
              {translate('pim_common.add')}
            </Button>
          </InputWithButton>
          {maxRecipientLimitReached && (
            <Helper inline={true} level="info">
              {translate('shared_catalog.recipients.max_limit_reached')}
            </Helper>
          )}
          {false === emailIsValid && (
            <Helper inline={true} level="error">
              {translate('shared_catalog.recipients.invalid_email')}
            </Helper>
          )}
          {true === emailIsDuplicated && (
            <Helper inline={true} level="error">
              {translate('shared_catalog.recipients.duplicates')}
            </Helper>
          )}
        </InputContainer>
      </Form>
      <SearchBar count={filteredRecipients.length} searchValue={searchValue} onSearchChange={setSearchValue} />
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
