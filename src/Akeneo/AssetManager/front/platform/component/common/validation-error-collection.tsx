import React from 'react';
import styled from 'styled-components';
import {AttributeCode} from 'akeneoassetmanager/platform/model/structure/attribute';
import {ValidationError, getValidationErrorsForAttribute} from 'akeneoassetmanager/platform/model/validation-error';
import {Separator} from 'akeneoassetmanager/application/component/app/separator';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {DangerIcon, getColor} from 'akeneo-design-system';

type ValidationErrorCollectionProps = {
  attributeCode: AttributeCode;
  context: Context;
  errors: ValidationError[];
};

const ErrorSection = styled.div`
  background-color: ${getColor('red', 20)};
  color: ${getColor('red', 100)};
  margin-top: 2px;
  height: 44px;
  width: 100%;
  display: flex;
  padding: 10px;
`;

const ErrorSeparator = styled(Separator)`
  border-color: ${getColor('red', 100)};
`;

const ErrorText = styled.div`
  font-size: 13px;
`;

//TODO RAC-413 replace this with DSM Helper?
export const ValidationErrorCollection = ({attributeCode, context, errors}: ValidationErrorCollectionProps) => {
  const errorCollection = getValidationErrorsForAttribute(attributeCode, context, errors);

  if (errorCollection.length === 0) {
    return null;
  }

  return (
    <React.Fragment>
      {errorCollection.map(({message}: ValidationError, index: number) => (
        <ErrorSection key={index}>
          <DangerIcon />
          <ErrorSeparator />
          <ErrorText>{message}</ErrorText>
        </ErrorSection>
      ))}
    </React.Fragment>
  );
};
