import { useState, useEffect } from "react";
import { getRuleDefinitionByCode } from "../../../fetch/RuleDefinitionFetcher";
import { getActivatedLocales } from "../../../fetch/LocaleFetcher";
import { RuleDefinition } from "../../../models/RuleDefinition";
import { Locale } from "../../../models/Locale";
import { Router } from "../../../dependenciesTools";

type Error = { exception: any; status: boolean };

const useInitEditRules = (ruleDefinitionCode: string, router: Router) => {
  const [error, setError] = useState<Error>({ exception: null, status: false });
  const [ruleDefinition, setRuleDefinition] = useState<RuleDefinition>();
  const [locales, setLocales] = useState<Locale[]>();

  useEffect(() => {
    Promise.all([
      getRuleDefinitionByCode(ruleDefinitionCode, router),
      getActivatedLocales(router),
    ])
      .then((response) => {
        setRuleDefinition(response[0]);
        setLocales(response[1]);
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
  };
};

export { useInitEditRules };
