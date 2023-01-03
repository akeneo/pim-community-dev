import React, {useMemo} from 'react';
import {Styled} from './Styled';
import {Violation} from '../validators';

type Props = {
  errors: Violation[];
};

const TabValidationErrors: React.FC<Props> = ({errors}) => {
  const displayedErrors = useMemo(
    () => errors?.map(({message}) => message).filter((value, index, self) => self.indexOf(value) === index),
    [errors]
  );

  return (
    <>
      {displayedErrors?.length > 0 && (
        <Styled.MainErrorHelper level="error">
          {displayedErrors.length > 1 ? (
            <Styled.ErrorList>
              {displayedErrors.map(message => (
                <li key={message}>{message}</li>
              ))}
            </Styled.ErrorList>
          ) : (
            <Styled.UniqueError>{displayedErrors[0]}</Styled.UniqueError>
          )}
        </Styled.MainErrorHelper>
      )}
    </>
  );
};

export {TabValidationErrors};
