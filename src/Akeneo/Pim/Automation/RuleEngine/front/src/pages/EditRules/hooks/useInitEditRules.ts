import {useState, useEffect} from 'react';
import {getRuleDefinitionByCode} from '../../../fetch/RuleDefinitionFetcher';
import {Locale, RuleDefinition} from '../../../models';
import {Router, Security, Translate} from '../../../dependenciesTools';
import {
  getAllScopes,
  IndexedScopes,
} from '../../../repositories/ScopeRepository';
import {
  getActivatedLocales,
  getUiLocales,
} from '../../../repositories/LocaleRepository';
import {ServerException} from '../../../exceptions';

type Error = {
  exception: any;
  status: boolean;
  statusCode: number;
};

const useInitEditRules = (
  ruleDefinitionCode: string,
  router: Router,
  security: Security,
  translate: Translate,
  setRuleDefinition: (ruleDefinition: RuleDefinition) => void
) => {
  const [error, setError] = useState<Error>({
    exception: null,
    status: false,
    statusCode: 500,
  });
  const [locales, setLocales] = useState<Locale[]>();
  const [uiLocales, setUiLocales] = useState<Locale[]>();
  const [scopes, setScopes] = useState<IndexedScopes>();

  useEffect(() => {
    if (!security.isGranted('pimee_catalog_rule_rule_edit_permissions')) {
      setError({
        exception: new Error(
          translate('pimee_catalog_rule.exceptions.edit_unauthorize')
        ),
        status: true,
        statusCode: 401,
      });

      return;
    }

    Promise.all([
      getRuleDefinitionByCode(ruleDefinitionCode, router),
      getActivatedLocales(router),
      getUiLocales(router),
      getAllScopes(router),
    ])
      .then(response => {
        setRuleDefinition(response[0]);
        setLocales(response[1]);
        setUiLocales(response[2]);
        setScopes(response[3]);
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
          setError({exception, status: true, statusCode: 500});
        }
      });
  }, []);

  return {
    error,
    locales,
    uiLocales,
    scopes,
  };
};

export {useInitEditRules};
