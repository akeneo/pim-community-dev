import React from 'react';

type ErrorBlockProps = {
  title: string;
  message: string;
  code: number;
};

const ErrorBlock = ({title, message, code}: ErrorBlockProps) => (
  <div className="AknInfoBlock AknInfoBlock--error">
    <img src="/bundles/pimui/images/illustration-error-404.svg" />
    <span className="AknInfoBlock-errorNumber AknInfoBlock-errorNumber--400">{code}</span>
    <h1>{title}</h1>
    <div className="AknMessageBox AknMessageBox--danger AknMessageBox--centered">{message}</div>
  </div>
);

export {ErrorBlock};
