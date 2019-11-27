import {FlowType} from './flow-type.enum';

export interface App {
    code: string;
    label: string;
    flowType: FlowType;
    image: string | null;
}
