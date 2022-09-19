import {IdentifierGenerator} from "../models";
import {Validator} from "./Validator";
import {validateIdentifierGeneratorCode} from "./IdentifierGeneratorCodeValidator";
import {validateTarget} from "./TargetValidator";

const validateIdentifierGenerator: Validator<IdentifierGenerator> = (identifierGenerator, path) => [
  ...validateIdentifierGeneratorCode(identifierGenerator.code, 'code'),
  ...validateTarget(identifierGenerator.target, 'target'),
];

export { validateIdentifierGenerator };
