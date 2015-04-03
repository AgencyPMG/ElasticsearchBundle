# PmgElasticsearchBundle

This is an extremely simple bundle to integrate Elasticsearch into Symfony. It
only provides some configuration and the elasticsearch client.

## Configuration

You'll use the `pmg_elasticsearch` key in your `config.yml` file. The options
are very similar to what is done with with elasticsearch itself.

```yaml
pmg_elasticsearch:
    connection_class: ~
```
