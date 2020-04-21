import React from "react";
import {FallbackAction} from "../../../models/FallbackAction";
import {ActionTemplate} from "./ActionTemplate";
import {InputText} from "../../../components/InputText";
import {ActionLineProps} from "../ActionLineProps";

type Props = {
  action: FallbackAction
} & ActionLineProps

const FallbackActionLine: React.FC<Props> = ({ translate , lineNumber, register}) => {
  return (
    <ActionTemplate
      translate={translate}
      title="Unknown Action"
      helper="This feature is under development. Please use the import to manage your rules."
      srOnly="This feature is under development. Please use the import to manage your rules."
    >
      <InputText name={`content.actions[${lineNumber}]`} ref={register} disabled readOnly/>
    </ActionTemplate>
  );
};

export { FallbackActionLine }
