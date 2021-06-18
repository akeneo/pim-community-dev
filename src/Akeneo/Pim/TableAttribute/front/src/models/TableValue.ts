export type TableCell = string | boolean | number;
export type TableRow = {[columnCode: string]: TableCell};
export type TableValue = TableRow[];
