import React from 'react';
import styled from 'styled-components';
// @todo pull-up master: change to '@akeneo-pim-community/shared'
import {AkeneoThemeProvider, Button, CloseIcon, WarningIcon, Key, useAutoFocus, useShortcut} from 'akeneosharedcatalog/akeneo-pim-community/shared';
// @todo pull-up master: change to '@akeneo-pim-community/legacy-bridge'
import {DependenciesProvider, useTranslate} from 'akeneosharedcatalog/akeneo-pim-community/legacy-bridge';
import {HeaderCell, LabelCell, Row, Table} from 'akeneosharedcatalog/common/Table';

type Recipient = {
  email: string;
};
type ValidationError = {
  email?: string;
};
type ValidationErrors = {
  [index: number]: ValidationError;
}
type RecipientsProps = {
  recipients: Recipient[];
  validationErrors: ValidationErrors;
  onRecipientsChange: (updatedRecipients: Recipient[]) => {};
};

const emailRegex = /\S+@\S+\.\S+/;
const isValidEmail = (email: string) => {
  return emailRegex.test(email);
};

const Body = styled.div``;
const Form = styled.div`
  align-items: baseline;
  display: flex;
  justify-content: center;
  padding: 50px 0 5px 0;
  width: 100%;
`;
const InputContainer = styled.div`
  display: flex;
  flex-direction: column;
`;
const Input = styled.input`
  border-radius: 2px;
  border: 1px solid #ccd1d8;
  color: #11324d;
  height: 40px;
  line-height: 40px;
  margin-right: 10px;
  padding: 0 8px;
  width: 100%;
  width: 400px;
  z-index: 1;
`;
const ErrorMessage = styled.span`
  color: #d4604f;
  display: inline-block;
  font-size: 11px;
  font-style: normal;
  line-height: 13px;
  margin: 0 0 0 20px;
`;
const InputError = styled.div`
  align-items: center;
  color: #d4604f;
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

const Container = ({
  ...props
}: RecipientsProps) => {
  return (
    <DependenciesProvider>
      <AkeneoThemeProvider>
        <Recipients {...props}/>
      </AkeneoThemeProvider>
    </DependenciesProvider>
  );
};

const Recipients = ({
  recipients,
  validationErrors,
  onRecipientsChange
}: RecipientsProps) => {
  const __ = useTranslate();
  const [recipientToAdd, setRecipientToAdd] = React.useState('');
  const [emailIsValid, setEmailIsValid] = React.useState(true);
  const inputRef = React.useRef<null|HTMLInputElement>(null);
  useAutoFocus(inputRef);

  const handleAddNewRecipient = React.useCallback(() => {
    if (isValidEmail(recipientToAdd)) {
      onRecipientsChange([...recipients, {email: recipientToAdd}]);
    } else {
      setEmailIsValid(false);
    }
  }, [onRecipientsChange, recipients, recipientToAdd, setEmailIsValid]);

  useShortcut(Key.Enter, handleAddNewRecipient);
  useShortcut(Key.NumpadEnter, handleAddNewRecipient);

  return (
    <Body>
      <Form>
        <InputContainer>
          <Input
            ref={inputRef}
            placeholder={ __('shared_catalog.recipients.add_recipient') }
            value={recipientToAdd}
            onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
              setEmailIsValid(true);
              setRecipientToAdd(event.currentTarget.value);
            }}
          />
          {false === emailIsValid &&
            <InputError>
              <WarningIcon color={'#d4604f'} size={18}/>
              { __('shared_catalog.recipients.invalid_email') }
            </InputError>
          }
        </InputContainer>
        <Button onClick={handleAddNewRecipient}>
          {__('pim_common.add')}
        </Button>
      </Form>
      <Table title="recipients">
        <thead>
          <Row>
            <HeaderCell>Email</HeaderCell>
            <HeaderCell/>
          </Row>
        </thead>
        <tbody>
          {recipients.map((recipient, index) => (
            <Row key={recipient.email}>
              <Cell>
                {recipient.email}
                {validationErrors[index] &&
                  <ErrorMessage>{validationErrors[index].email}</ErrorMessage>
                }
              </Cell>
              <ActionCell>
                <CloseIcon
                  onClick={() => {
                    onRecipientsChange(recipients.filter(currentRecipient => currentRecipient !== recipient));
                  }}
                  size={20}
                  title={__('pim_common.delete')}
                />
              </ActionCell>
            </Row>
          ))}
        </tbody>
      </Table>
    </Body>
  );
};

export {Container as default, Recipients};
