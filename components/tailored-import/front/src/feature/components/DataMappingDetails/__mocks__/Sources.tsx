import React from 'react';
import {SourcesProps} from '../Sources';

const Sources = ({onSourcesChange, validationErrors}: SourcesProps) => {
  return (
    <>
      <h1>Sources</h1>
      <button onClick={() => onSourcesChange(['source2'])}>Set source</button>
      {validationErrors.map((error, index) => (
        <div key={index}>{error.messageTemplate}</div>
      ))}
    </>
  );
};

export {Sources};
