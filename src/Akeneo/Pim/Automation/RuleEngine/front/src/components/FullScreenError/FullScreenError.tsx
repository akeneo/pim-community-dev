import React from 'react';
import { Translate } from '../../dependenciesTools';

type Props = {
  statusCode: number;
  message: string;
  translate: Translate;
};

const FullScreenError: React.FC<Props> = ({
  statusCode,
  message,
  translate,
}) => {
  return (
    <div className='AknInfoBlock AknInfoBlock--error'>
      <img
        src={`/bundles/pimui/images/illustration-error-${
          statusCode >= 400 && statusCode < 500 ? '404' : '503'
        }.svg`}
      />
      <span
        className={`AknInfoBlock-errorNumber AknInfoBlock-errorNumber--${
          statusCode >= 400 && statusCode < 500 ? '400' : '500'
        }`}>
        {statusCode}
      </span>
      <h1>{translate('error.exception', { status_code: statusCode })}</h1>
      <div className='AknMessageBox AknMessageBox--danger AknMessageBox--centered'>
        {translate(message)}
      </div>
    </div>
  );
};

export { FullScreenError };
