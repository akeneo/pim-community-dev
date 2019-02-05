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


{{- define "pim.extraLabels" }}
{{- range $key, $value := .Values.global.extraLabels }}
{{ $key }}: {{ $value | quote }}
{{- end }}
{{- range $key, $value := .Values.common.extraLabels }}
{{ $key }}: {{ $value | quote }}
{{- end }}
{{- range $key, $value := .Values.pim.extraLabels }}
{{ $key }}: {{ $value | quote }}
{{- end }}
{{- end }}
