import * as React from 'react';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import styled from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {AttributeCode} from 'akeneoassetmanager/platform/model/structure/attribute';
import {ValidationError, getValidationErrorsForAttribute} from 'akeneoassetmanager/platform/model/validation-error';
import ErrorIcon from 'akeneoassetmanager/platform/component/visual/icon/error';
import {Separator} from 'akeneoassetmanager/application/component/app/separator';
import {Context} from 'akeneoassetmanager/domain/model/context';

type ValidationErrorCollectionProps = {
  attributeCode: AttributeCode;
  context: Context;
  errors: ValidationError[];
};

const ErrorSection = styled.div`
  background-color: ${(props: ThemedProps<void>) => props.theme.color.red20};
  color: ${(props: ThemedProps<void>) => props.theme.color.red100};
  margin-top: 2px;
  height: 44px;
  width: 100%;
  display: flex;
  padding: 10px;
`;

const ErrorSperator = styled(Separator)`
  border-color: ${(props: ThemedProps<void>) => props.theme.color.red100};
`;

const ErrorText = styled.div`
  font-size: 13px;
`;

export const ValidationErrorCollection = ({attributeCode, context, errors}: ValidationErrorCollectionProps) => {
  const errorCollection = getValidationErrorsForAttribute(attributeCode, context, errors);

  if (errorCollection.length === 0) {
    return null;
  }

  return (
    <React.Fragment>
      {errorCollection.map(({message}: ValidationError, index: number) => (
        <ErrorSection key={index}>
          <ErrorIcon color={akeneoTheme.color.red100} />
          <ErrorSperator />
          <ErrorText>{message}</ErrorText>
        </ErrorSection>
      ))}
    </React.Fragment>
  );
};
