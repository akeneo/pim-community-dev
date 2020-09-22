type FormData = {
  code: string;
  priority: string;
  enabled: boolean;
  labels: {
    [key: string]: string;
  };
  content: { conditions: any[]; actions: any[] };
  execute_on_save?: boolean;
};

export { FormData };
