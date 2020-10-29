export type Payload = {
  labels: {[key: string]: string};
  code: string;
  priority: number;
  enabled: boolean;
  content: {
    conditions: {};
    actions: {};
  };
};
