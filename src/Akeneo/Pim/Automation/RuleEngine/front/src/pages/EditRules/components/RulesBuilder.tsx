import React from "react";
import { Translate } from "../../../dependenciesTools";
import { RuleProductSelection } from "./RuleProductSelection";
import {RuleDefinition} from "../../../models/RuleDefinition";
import {Action} from "../../../models/Action";
import {Locale} from "../../../models/Locale";
import {IndexedScopes} from "../../../fetch/ScopeFetcher";

type Props = {
  register: any;
  translate: Translate;
  ruleDefinition: RuleDefinition;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: string;
};

const ActionLine: React.FC<{ action: Action, translate: Translate }> = ({ action, translate }) => {
  const Line = action.module;

  return (
    <Line
      action={action}
      translate={translate}
    />
  );
};

const RulesBuilder: React.FC<Props> = ({
    register,
    translate,
    ruleDefinition,
    locales,
    scopes,
    currentCatalogLocale
  }) => {
  return (
    <>
      <RuleProductSelection
        register={register}
        ruleDefinition={ruleDefinition}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={currentCatalogLocale}
      />
      {ruleDefinition.actions.map((action, i) => {
        return (
          <ActionLine
            action={action}
            translate={translate}
            key={`action_${i}`}
          />
        );
      })}
    </>
  );
};

RulesBuilder.displayName = "RulesBuilder";

export { RulesBuilder };
