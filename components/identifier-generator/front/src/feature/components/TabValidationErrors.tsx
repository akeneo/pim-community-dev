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
            <p style={{margin: 0}}>{displayedErrors[0]}</p>
          )}
        </Styled.MainErrorHelper>
      )}
    </>
  );
};

export {TabValidationErrors};
