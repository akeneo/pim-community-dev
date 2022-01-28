{{/* vim: set filetype=mustache: */}}
{{/*
Expand the name of the chart.
*/}}
{{- define "elasticsearch.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 53 | trimSuffix "-" -}}
{{- end -}}

{{/*
Create a default fully qualified app name.
We truncate at 53 chars (63 - len("-discovery")) because some Kubernetes name fields are limited to 63 (by the DNS naming spec).
*/}}
{{- define "elasticsearch.fullName" -}}
{{- $name := default .Chart.Name .Values.nameOverride -}}
{{- printf "%s" $name -}}
{{- end -}}

{{/*
plugin installer template
*/}}
{{- define "plugin-installer" -}}
- name: es-plugin-install
  image: "{{ .Values.image.es.repository }}:{{ .Values.image.es.tag }}"
  imagePullPolicy: {{ .Values.image.es.pullPolicy }}
  securityContext:
    capabilities:
      add:
        - IPC_LOCK
        - SYS_RESOURCE
  command:
    - "sh"
    - "-c"
    - |
      {{- range .Values.cluster.plugins }}
      PLUGIN_NAME="{{ . }}"
      echo "Installing $PLUGIN_NAME..."
      if /usr/share/elasticsearch/bin/elasticsearch-plugin list | grep "$PLUGIN_NAME" > /dev/null; then
        echo "Plugin $PLUGIN_NAME already exists, skipping."
      else
        /usr/share/elasticsearch/bin/elasticsearch-plugin install -b $PLUGIN_NAME
      fi
      {{- end }}
  volumeMounts:
  - name: plugindir
    mountPath: /usr/share/elasticsearch/plugins/
  - name: config
    mountPath: /usr/share/elasticsearch/config/elasticsearch.yml
    subPath: elasticsearch.yml
{{- end -}}
