import React from 'react';
import styled from 'styled-components';
// @todo pull-up master: change to '@akeneo-pim-community/shared'
import {AkeneoThemeProvider, Button, CloseIcon, Key, useAutoFocus, useShortcut} from 'akeneosharedcatalog/akeneo-pim-community/shared';
import {HeaderCell, LabelCell, Row, Table} from 'akeneosharedcatalog/common/Table';

type Recipient = {
  email: string;
};
type ValidationError = {
  email?: string;
};
type RecipientsProps = {
  recipients: Recipient[];
  validationErrors: ValidationError[];
  onRecipientsChange: (updatedRecipients: Recipient[]) => {};
};

const Container = styled.div``;
const Form = styled.div`
  align-items: center;
  display: flex;
  justify-content: center;
  padding: 50px 0 5px 0;
  width: 100%;
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

const ActionCell = styled(LabelCell)`
  width: 50px !important;
`;

const Cell = styled(LabelCell)`
  width: auto !important;
`;

const Recipients = ({
  recipients,
  validationErrors,
  onRecipientsChange
}: RecipientsProps) => {
  const [recipientToAdd, setRecipientToAdd] = React.useState('');
  const inputRef = React.useRef<null|HTMLInputElement>(null);
  useAutoFocus(inputRef);

  const handleAddNewRecipient = React.useCallback(() => {
    onRecipientsChange([...recipients, {email: recipientToAdd}]);
  }, [onRecipientsChange, recipients, recipientToAdd]);

  useShortcut(Key.Enter, handleAddNewRecipient);
  useShortcut(Key.NumpadEnter, handleAddNewRecipient);

  return (
    <AkeneoThemeProvider>
      <Container>
        <Form>
          <Input
            ref={inputRef}
            placeholder="Add a recipient"
            value={recipientToAdd}
            onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
              setRecipientToAdd(event.currentTarget.value);
            }}
          />
          <Button onClick={handleAddNewRecipient}>
            Add
          </Button>
        </Form>
        <Table>
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
                  />
                </ActionCell>
              </Row>
            ))}
          </tbody>
        </Table>
      </Container>
    </AkeneoThemeProvider>
  );
};

export {Recipients};
