import React from "react";
import { Input, InputProps } from "./Input";

const InputNumber = React.forwardRef<HTMLInputElement, InputProps>(
  (props, forwardedRef: React.Ref<HTMLInputElement>) => {
    return (
      <Input
        className="AknTextField"
        type="number"
        ref={forwardedRef}
        {...props}
      />
    );
  }
);

export { InputNumber };
