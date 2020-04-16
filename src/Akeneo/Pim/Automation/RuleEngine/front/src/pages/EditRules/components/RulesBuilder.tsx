import React from "react";
import { Translate } from "../../../dependenciesTools";
import { RuleProductSelection } from "./RuleProductSelection";
import {RuleDefinition} from "../../../models/RuleDefinition";
import {Action} from "../../../models/Action";

type Props = {
  translate: Translate;
  ruleDefinition: RuleDefinition;
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

const RulesBuilder: React.FC<Props> = ({ translate, ruleDefinition }) => {
  return (
    <>
      <RuleProductSelection ruleDefinition={ruleDefinition} translate={translate} />
      {ruleDefinition.actions.map((action, i) => {
        return <ActionLine
          action={action}
          translate={translate}
          key={`action_${i}`}/>
      })}
    </>
  );
};

RulesBuilder.displayName = "RulesBuilder";

export { RulesBuilder };
