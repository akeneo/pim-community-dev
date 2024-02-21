import React from 'react';
import {Violation} from '../../validators';
import {IdentifierGenerator} from '../../models';

type CreateOrEditGeneratorProps = {
  initialGenerator: IdentifierGenerator;
  mainButtonCallback: (identifierGenerator: IdentifierGenerator) => void;
  validationErrors: Violation[];
};

const CreateOrEditGeneratorPage: React.FC<CreateOrEditGeneratorProps> = ({
  initialGenerator,
  mainButtonCallback,
  validationErrors,
}) => {
  return (
    <>
      <div>CreateOrEditGeneratorPage</div>
      <div>{JSON.stringify(initialGenerator)}</div>
      <button onClick={() => mainButtonCallback(initialGenerator)}>Main button</button>
      {validationErrors && (
        <ul>
          {validationErrors.map(({path, message}) => (
            <li key={`${path}${message}`}>
              {path} {message}
            </li>
          ))}
        </ul>
      )}
    </>
  );
};

export {CreateOrEditGeneratorPage};
