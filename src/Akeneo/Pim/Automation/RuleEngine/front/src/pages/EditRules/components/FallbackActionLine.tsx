import React from "react";
import {FallbackAction} from "../../../models/FallbackAction";
import {Translate} from "../../../dependenciesTools";
import {ActionTemplate} from "./ActionTemplate";

type Props = {
  action: FallbackAction,
  translate: Translate,
}

const FallbackActionLine: React.FC<Props> = ({ action, translate }) => {
  return (
    <ActionTemplate
      translate={translate}
      title="Unknown Action"
      helper="This feature is under development. Please use the import to manage your rules."
      srOnly="This feature is under development. Please use the import to manage your rules."
    >
      {JSON.stringify(action.json)}
    </ActionTemplate>
  );
};

export { FallbackActionLine }
