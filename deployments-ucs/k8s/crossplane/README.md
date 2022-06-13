export GCP_PROJECT=akecld-blackhawk-sandbox
export SA=ucs-crossplane-svc-account
export GCP_SVC_ACCOUNT="${SA}@${GCP_PROJECT}.iam.gserviceaccount.com"  
export KEY_FILE="${SA}-keyfile.json"
export PROVIDER_NAMESPACE=crossplane-system

gcloud iam service-accounts create --project ${GCP_PROJECT} ${SA}
gcloud iam service-accounts keys create --iam-account ${GCP_SVC_ACCOUNT} --project ${GCP_PROJECT} ${KEY_FILE}
gcloud projects add-iam-policy-binding ${GCP_PROJECT} --member "serviceAccount:${GCP_SVC_ACCOUNT}" --role="roles/iam.serviceAccountAdmin"
gcloud projects add-iam-policy-binding ${GCP_PROJECT} --member "serviceAccount:${GCP_SVC_ACCOUNT}" --role="roles/container.admin"
gcloud projects add-iam-policy-binding ${GCP_PROJECT} --member "serviceAccount:${GCP_SVC_ACCOUNT}" --role="roles/compute.networkAdmin"

cat > gcp-authentication.yaml <<EOF
apiVersion: v1
kind: Secret
metadata:
  name: gcp-account-creds
  namespace: ${PROVIDER_NAMESPACE}
type: Opaque
data:
  credentials: $(base64 ${KEY_FILE} | tr -d "\n")
EOF

cat > provider-jet-gcp.yaml <<EOF
apiVersion: pkg.crossplane.io/v1
kind: Provider
metadata:
  name: provider-jet-gcp
spec:
  package: crossplane/provider-jet-gcp:v0.2.0-preview

EOF

cat > provider-config-jet-gcp.yaml <<EOF
apiVersion: gcp.jet.crossplane.io/v1alpha1
kind: ProviderConfig
metadata:
  name: provider-config-jet-gcp
spec:
  projectID: ${GCP_PROJECT}
  credentials:
    source: Secret
    secretRef:
      namespace: ${PROVIDER_NAMESPACE}
      name: gcp-account-creds
      key: credentials
EOF
