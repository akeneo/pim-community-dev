import React, {useState, useEffect, useRef, useCallback, ChangeEvent} from 'react';
import styled from 'styled-components';
// @todo pull-up master: change to '@akeneo-pim-community/shared'
import {
  AkeneoThemeProvider,
  Button,
  CloseIcon,
  WarningIcon,
  Key,
  useAutoFocus,
  useShortcut,
  useAkeneoTheme,
  SearchBar,
  HelperLevel,
  HelperRibbon,
  AkeneoThemedProps,
  NoResultsIllustration,
  UserSurveyIllustration,
  InfoIcon,
} from 'akeneosharedcatalog/akeneo-pim-community/shared';
// @todo pull-up master: change to '@akeneo-pim-community/legacy-bridge'
import {DependenciesProvider, useTranslate} from 'akeneosharedcatalog/akeneo-pim-community/legacy-bridge';
import {HeaderCell, LabelCell, Row, Table} from 'akeneosharedcatalog/common/Table';

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

const Input = styled.input<{isInvalid: boolean}>`
  border-radius: 2px;
  border: 1px solid;
  border-color: ${({theme, isInvalid}: AkeneoThemedProps & {isInvalid: boolean}) =>
    isInvalid ? theme.color.red100 : theme.color.grey80};
  color: ${({theme}: AkeneoThemedProps) => theme.color.grey140};
  height: 40px;
  line-height: 40px;
  margin-right: 10px;
  padding: 0 8px;
  width: 400px;
  z-index: 1;

  :disabled {
    color: ${({theme}: AkeneoThemedProps) => theme.color.grey60};
    background-color: ${({theme}: AkeneoThemedProps) => theme.color.grey60};
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

const InputError = styled.div`
  align-items: center;
  color: ${({theme}: AkeneoThemedProps) => theme.color.red100};
  display: flex;
  font-size: 11px;
  font-style: normal;
  line-height: 13px;
  margin: 6px 0;

  svg {
    margin: 0 6px 0 0;
  }
`;

const ActionCell = styled(LabelCell)`
  width: 50px !important;

  svg {
    margin-top: 6px;
  }
`;

const Cell = styled(LabelCell)`
  width: auto !important;
`;

const NoResults = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  color: ${({theme}: AkeneoThemedProps) => theme.color.grey140};
  line-height: 28px;
  margin-top: 40px;
`;

const InputHelper = styled.div`
  display: flex;
  align-items: center;
  font-size: ${({theme}: AkeneoThemedProps) => theme.fontSize.small};
  padding: 3px 0;

  svg {
    margin-right: 4px;
  }
`;

const InputWithButton = styled.div`
  display: flex;
  align-items: center;
  margin-top: 8px;
`;

const Container = (props: RecipientsProps) => {
  return (
    <DependenciesProvider>
      <AkeneoThemeProvider>
        <Recipients {...props} />
      </AkeneoThemeProvider>
    </DependenciesProvider>
  );
};

const Recipients = ({recipients, validationErrors, onRecipientsChange}: RecipientsProps) => {
  const translate = useTranslate();
  const theme = useAkeneoTheme();
  const [currentRecipients, setCurrentRecipients] = useState<Recipient[]>(recipients);
  const [recipientToAdd, setRecipientToAdd] = useState<string>('');
  const [emailIsValid, setEmailIsValid] = useState<boolean>(true);
  const [emailIsDuplicated, setEmailIsDuplicated] = useState<boolean>(false);
  const [searchValue, setSearchValue] = useState<string>('');
  const inputRef = useRef<null | HTMLInputElement>(null);

  const handleAddNewRecipient = useCallback(() => {
    if (emailIsDuplicated || !emailIsValid || '' === recipientToAdd) return;

    if (isValidEmail(recipientToAdd)) {
      setCurrentRecipients(currentRecipients => [...currentRecipients, {email: recipientToAdd}]);
      setRecipientToAdd('');
    } else {
      setEmailIsValid(false);
    }
  }, [recipientToAdd, setEmailIsValid, emailIsDuplicated]);

  useAutoFocus(inputRef);
  useShortcut(Key.Enter, handleAddNewRecipient);
  useShortcut(Key.NumpadEnter, handleAddNewRecipient);

  useEffect(() => {
    setEmailIsDuplicated(currentRecipients.map(recipient => recipient.email).includes(recipientToAdd));
  }, [recipientToAdd, currentRecipients]);

  useEffect(() => {
    onRecipientsChange(currentRecipients);
  }, [currentRecipients]);

  const filteredRecipients = currentRecipients.filter(
    recipient => -1 !== recipient.email.toLowerCase().indexOf(searchValue.toLowerCase())
  );

  const maxRecipientLimitReached = MAX_RECIPIENT_COUNT <= currentRecipients.length;

  return (
    <Body>
      <HelperRibbon level={HelperLevel.HELPER_LEVEL_INFO}>{translate('shared_catalog.recipients.helper')}</HelperRibbon>
      {'string' === typeof validationErrors && (
        <HelperRibbon level={HelperLevel.HELPER_LEVEL_ERROR}>{translate(validationErrors)}</HelperRibbon>
      )}
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
              onChange={(event: ChangeEvent<HTMLInputElement>) => {
                setEmailIsValid(true);
                setRecipientToAdd(event.currentTarget.value);
              }}
            />
            <Button
              color="grey"
              onClick={handleAddNewRecipient}
              disabled={emailIsDuplicated || '' === recipientToAdd || maxRecipientLimitReached}
              outline={true}
            >
              {translate('pim_common.add')}
            </Button>
          </InputWithButton>
          {maxRecipientLimitReached && (
            <InputHelper>
              <InfoIcon size={18} color={theme.color.blue100} />
              {translate('shared_catalog.recipients.max_limit_reached')}
            </InputHelper>
          )}
          {false === emailIsValid && (
            <InputError>
              <WarningIcon color={theme.color.red100} size={18} />
              {translate('shared_catalog.recipients.invalid_email')}
            </InputError>
          )}
          {true === emailIsDuplicated && (
            <InputError>
              <WarningIcon color={theme.color.red100} size={18} />
              {translate('shared_catalog.recipients.duplicates')}
            </InputError>
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
          {filteredRecipients.map((recipient, index) => (
            <Row key={recipient.email}>
              <Cell>
                {recipient.email}
                {validationErrors[index] && <ErrorMessage>{validationErrors[index].email}</ErrorMessage>}
              </Cell>
              <ActionCell>
                <CloseIcon
                  onClick={() => {
                    setCurrentRecipients(currentRecipients =>
                      currentRecipients.filter(currentRecipient => currentRecipient !== recipient)
                    );
                  }}
                  size={20}
                  title={translate('pim_common.delete')}
                />
              </ActionCell>
            </Row>
          ))}
        </tbody>
      </Table>
      {0 === currentRecipients.length ? (
        <NoResults>
          <UserSurveyIllustration size={80} />
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
    </Body>
  );
};

export {Container as default, Recipients};
