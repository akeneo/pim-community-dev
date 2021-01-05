import React from 'react';
import {ClientErrorIllustration, ServerErrorIllustration} from 'akeneo-design-system';

type PageErrorBlockProps = {
  title: string;
  message: string;
  code: number;
};

const PageErrorBlock = ({title, message, code}: PageErrorBlockProps) => {
  const isClientError = code >= 400 && code < 500;

  return (
    <div className="AknInfoBlock AknInfoBlock--error">
      {isClientError ? (
        <ClientErrorIllustration width="100%" height="100%" />
      ) : (
        <ServerErrorIllustration width="100%" height="100%" />
      )}
      <span className={`AknInfoBlock-errorNumber AknInfoBlock-errorNumber--${isClientError ? '400' : '500'}`}>
        {code}
      </span>
      <h1>{title}</h1>
      <div className="AknMessageBox AknMessageBox--danger AknMessageBox--centered">{message}</div>
    </div>
  );
};

export {PageErrorBlock};
