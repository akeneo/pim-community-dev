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
Define the tag of the PIM Enterprise Cloud image
Can be with or without the Onboarder bundle activated
*/}}
{{- define "pim.imageNameAndTag" -}}
{{- if .Values.onboarder.enabled -}}
{{- printf "%s:%s-onboarder" .Values.image.pim.repository .Values.image.pim.tag -}}
{{- else -}}
{{- printf "%s:%s" .Values.image.pim.repository .Values.image.pim.tag -}}
{{- end -}}
{{- end -}}

{{/*
    Define common labels for Kubernetes objects
    See K8s common labels here : https://kubernetes.io/docs/concepts/overview/working-with-objects/common-labels/
*/}}
{{- define "pim.standardLabels" -}}
app: pim # Deprecated. Cf https://www.notion.so/akeneo/Tagging-strategy-782b4ae037c44d4884b02c8c62e81117
app.kubernetes.io/name: pim
chart: "{{ .Chart.Name }}-{{ .Chart.Version | replace "+" "_" }}"
helm.sh/chart: "{{ .Chart.Name }}-{{ .Chart.Version | replace "+" "_" }}"
release: "{{ .Release.Name }}" # Ex: srnt-invivo
product_reference: serenity
product_version: "{{ .Chart.Version | replace "+" "_" }}"
heritage: "{{ .Release.Service }}" # Deprecated
{{- range $key, $value := .Values.global.extraLabels }}
{{ $key }}: {{ $value | quote }}
{{- end }}
{{- end -}}

{{- define "onboarder.standardLabels" -}}
app: onboarder # Deprecated. Cf https://www.notion.so/akeneo/Tagging-strategy-782b4ae037c44d4884b02c8c62e81117
app.kubernetes.io/name: onboarder
chart: "{{ .Chart.Name }}-{{ .Chart.Version | replace "+" "_" }}"
helm.sh/chart: "{{ .Chart.Name }}-{{ .Chart.Version | replace "+" "_" }}"
release: "{{ .Release.Name }}" # Ex: srnt-invivo
product_reference: serenity
product_version: "{{ .Chart.Version | replace "+" "_" }}"
heritage: "{{ .Release.Service }}" # Deprecated
{{- range $key, $value := .Values.global.extraLabels }}
{{ $key }}: {{ $value | quote }}
{{- end }}
{{- end -}}