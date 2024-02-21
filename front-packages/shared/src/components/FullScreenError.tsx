import React from 'react';
import {ClientErrorIllustration, ServerErrorIllustration} from 'akeneo-design-system';

type FullScreenErrorProps = {
  title: string;
  message: string;
  code: number;
};

const FullScreenError = ({title, message, code}: FullScreenErrorProps) => {
  const isClientError = code >= 400 && code < 500;

  return (
    <div className="AknInfoBlock AknInfoBlock--error">
      {isClientError ? (
        <ClientErrorIllustration width="auto" height="auto" />
      ) : (
        <ServerErrorIllustration width="auto" height="auto" />
      )}
      <span className={`AknInfoBlock-errorNumber AknInfoBlock-errorNumber--${isClientError ? '400' : '500'}`}>
        {code}
      </span>
      <h1>{title}</h1>
      <div className="AknMessageBox AknMessageBox--danger AknMessageBox--centered">{message}</div>
    </div>
  );
};

export {FullScreenError};
