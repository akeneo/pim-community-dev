import { useState, useEffect } from "react";
import { getRuleDefinitionByCode } from "../../../fetch/RuleDefinitionFetcher";
import { getActivatedLocales } from "../../../fetch/LocaleFetcher";
import { RuleDefinition } from "../../../models";
import { Locale } from "../../../models";
import { Router } from "../../../dependenciesTools";
import {getAllScopes, IndexedScopes} from "../../../fetch/ScopeFetcher";

type Error = { exception: any; status: boolean };

const useInitEditRules = (ruleDefinitionCode: string, router: Router) => {
  const [error, setError] = useState<Error>({ exception: null, status: false });
  const [ruleDefinition, setRuleDefinition] = useState<RuleDefinition>();
  const [locales, setLocales] = useState<Locale[]>();
  const [scopes, setScopes] = useState<IndexedScopes>();

  useEffect(() => {
    Promise.all([
      getRuleDefinitionByCode(ruleDefinitionCode, router),
      getActivatedLocales(router),
      getAllScopes(router),
    ])
      .then((response) => {
        setRuleDefinition(response[0] as RuleDefinition);
        setLocales(response[1] as Locale[]);
        setScopes(response[2] as IndexedScopes);
      })
      .catch((exception) => {
        setError({ exception, status: true });
        console.error(exception);
      });
  }, []);

  return {
    error,
    locales,
    ruleDefinition,
    scopes,
  };
};

export { useInitEditRules };
