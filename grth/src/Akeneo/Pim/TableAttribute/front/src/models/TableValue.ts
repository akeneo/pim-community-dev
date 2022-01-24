import {MeasurementValue} from "./MeasurementFamily";

export type TableCell = string | boolean | number | MeasurementValue;
export type TableRow = {[columnCode: string]: TableCell};
export type TableValue = TableRow[];
