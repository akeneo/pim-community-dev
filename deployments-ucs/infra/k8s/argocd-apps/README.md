# Usage of values files
Values files are combined in the following order (if present):
- values.yaml (defaults)
- values-${_CLUSTER_ENV}.yaml (dev/prod)
- values-${_GOOGLE_CLUSTER_REGION}.yaml (i.e. values-europe-west1.yaml)
- values-${_GOOGLE_PROJECT_ID}.yaml (i.e. values-akecld-prd-pim-saas-dev.yaml)
- values-${_CLUSTER_NAME}.yaml