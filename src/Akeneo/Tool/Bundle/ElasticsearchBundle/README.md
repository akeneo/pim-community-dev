# ElasticsearchBundle

Stupid and simple [PHP Elasticsearch](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html) wrapper for the Symfony world.

The only purpose of this bundle is to be able to launch native Elasticsearch queries through the official PHP client provided as a Symfony service. For that, it provides the following:
    - a PHP Elasticsearch client service
    - a way to load the index configuration from several YAML files
  
No support of any sort of query builder is provided. Neither of automatically mapping entities to Elasticsearch documents. For such features, please take
a look at the excellent [ONGR Elasticsearch Bundle](https://github.com/ongr-io/ElasticsearchBundle) and [ElasticsearchDSL](https://github.com/ongr-io/ElasticsearchDSL) packages.
We didn't use those packages for two main reasons:
    - they require Symfony 2.8, which, at the time of this README is written, is not compatible with our stack
    - we wanted to run pure simple and native Elasticsearch queries
    
This bundle is intended to remain simple and stupid. If at some point, we need more advanced features, we should consider replacing this bundle by something more powerful that already exists.

## Loading the index configuration

Index configuration can be loaded via separate YAML files.

```yaml
// app/config.yml


akeneo_elasticsearch:
    configuration_files:
        - 'path/to/a/configuration_file.yml'
        - 'path/to/another/configuration_file.yml'
```

All different configuration files are merged during this process. 
To learn more, please look at the specs examples of {@link src/Akeneo/Bundle/ElasticsearchBundle/spec/IndexConfiguration/LoaderSpec.php} to understand.

That allows, for instance, to add a custom configuration in top of Akeneo's default configuration.

## Configuration reference

```
# Default configuration for extension with alias: "akeneo_elasticsearch"
akeneo_elasticsearch:

    # Inline hosts of the Elasticsearch nodes. See https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#_inline_host_configuration. If you have a single host, you can use a string here. Otherwise, use an array.
    hosts:                [] # Required
    indexes:
        # An index name.
        index_name:           ~ # Required

        # Paths of the YAML files to configure the index. See https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html and src/Akeneo/Bundle/ElasticsearchBundle/IndexConfiguration/IndexConfiguration.php.
        configuration_files:  [] # Required

        # Name of the symfony service for this client that will be automatically registered in the symfony container.
        service_name:         ~ # Required
```
