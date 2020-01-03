import {FlowType} from './flow-type.enum';

export type Connection = {
    code: string;
    label: string;
    flowType: FlowType;
    image: string | null;
};
