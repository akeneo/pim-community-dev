import { useState, useEffect } from 'react';
import { getRuleDefinitionByCode } from '../../../fetch/RuleDefinitionFetcher';
import { Locale } from '../../../models';
import { Router } from '../../../dependenciesTools';
import {
  getAllScopes,
  IndexedScopes,
} from '../../../repositories/ScopeRepository';
import { getActivatedLocales } from '../../../repositories/LocaleRepository';
import { ServerException } from '../../../exceptions';

type Error = {
  exception: any;
  status: boolean;
  statusCode: number;
};

const useInitEditRules = (ruleDefinitionCode: string, router: Router, setRuleDefinition: any) => {
  const [error, setError] = useState<Error>({
    exception: null,
    status: false,
    statusCode: 500,
  });
  const [locales, setLocales] = useState<Locale[]>();
  const [scopes, setScopes] = useState<IndexedScopes>();

  useEffect(() => {
    Promise.all([
      getRuleDefinitionByCode(ruleDefinitionCode, router),
      getActivatedLocales(router),
      getAllScopes(router),
    ])
      .then(response => {
        setRuleDefinition(response[0]);
        setLocales(response[1]);
        setScopes(response[2]);
      })
      .catch(exception => {
        if (exception instanceof ServerException) {
          setError({
            exception,
            status: true,
            statusCode: exception.statusCode,
          });
        } else {
          console.error(exception);
          setError({ exception, status: true, statusCode: 500 });
        }
      });
  }, []);

  return {
    error,
    locales,
    scopes,
  };
};

export { useInitEditRules };
