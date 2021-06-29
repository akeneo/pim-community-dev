type Operation = {
  id: string;
  date: string;
  username: string;
  type: string;
  label: string;
  status: string;
  warningCount: string;
  statusLabel: string;
  tracking: {
    currentStep: number;
    totalSteps: number;
  };
  canSeeReport: boolean;
};

export {Operation};
