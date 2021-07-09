{{/* vim: set filetype=mustache: */}}
{{/*
Expand the name of the chart.
*/}}
{{- define "pim.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" -}}
{{- end -}}

{{/*
Create a default fully qualified app name.
We truncate at 63 chars because some Kubernetes name fields are limited to this (by the DNS naming spec).
*/}}
{{- define "pim.fullName" -}}
{{- $name := default .Chart.Name .Values.nameOverride -}}
{{- printf "%s-%s" .Release.Name $name | trunc 53 | trimSuffix "-" -}}
{{- end -}}

{{- define "pim.extraLabels" -}}
{{- range $key, $value := .Values.global.extraLabels }}
{{ $key }}: {{ $value | quote }}
{{- end -}}
{{- range $key, $value := .Values.common.extraLabels }}
{{ $key }}: {{ $value | quote }}
{{- end -}}
{{- range $key, $value := .Values.pim.extraLabels }}
{{ $key }}: {{ $value | quote }}
{{- end -}}
{{- end -}}

{{/*
Define the name of the PIM Enterprise dev Docker image
*/}}
{{- define "pim.imageName" -}}
{{- printf "%s" .Values.image.pim.repository -}}
{{- end -}}

{{/*
Define the tag of the PIM Enterprise dev Docker image
Can be with or without the Onboarder bundle activated
*/}}
{{- define "pim.imageNameAndTag" -}}
{{- printf "%s:%s" (include "pim.imageName" .) .Values.image.pim.tag -}}
{{- end -}}

{{/*
Define standard labels for PIM objects
*/}}
{{- define "pim.standardLabels" }}
app: pim # Deprecated. Cf https://www.notion.so/akeneo/Tagging-strategy-782b4ae037c44d4884b02c8c62e81117
app.kubernetes.io/name: pim
app.kubernetes.io/managed-by: "{{ .Release.Service }}"
chart: "{{ .Chart.Name }}-{{ .Chart.Version | replace "+" "_" }}"
helm.sh/chart: "{{ .Chart.Name }}-{{ .Chart.Version | replace "+" "_" }}"
release: "{{ .Release.Name }}" # Ex: srnt-invivo
product_reference: serenity
product_version: "{{ .Chart.Version | replace "+" "_" }}"
heritage: "{{ .Release.Service }}" # Deprecated
{{- end }}

{{/*
Define standard labels for Onboarder objects
*/}}
{{- define "onboarder.standardLabels" }}
app: onboarder # Deprecated. Cf https://www.notion.so/akeneo/Tagging-strategy-782b4ae037c44d4884b02c8c62e81117
app.kubernetes.io/name: onboarder
app.kubernetes.io/managed-by: "{{ .Release.Service }}"
chart: "{{ .Chart.Name }}-{{ .Chart.Version | replace "+" "_" }}"
helm.sh/chart: "{{ .Chart.Name }}-{{ .Chart.Version | replace "+" "_" }}"
release: "{{ .Release.Name }}" # Ex: akob-jdgroup
product_reference: onboarder
product_version: "{{ .Chart.Version | replace "+" "_" }}"
heritage: "{{ .Release.Service }}" # Deprecated
{{- end }}
