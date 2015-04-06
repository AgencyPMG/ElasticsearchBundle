# PmgElasticsearchBundle

This is an extremely simple bundle to integrate Elasticsearch into Symfony. It
only provides some configuration and the elasticsearch client.

## Installation

Grab the bundle with composer: 

```bash
composer require pmg/elasticsearch-bundle ~1.0
```

And enable it in your `AppKernel`.

```php
<?php
// app/AppKernel.php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new PMG\ElasticsearchBundle\PmgElasticsearchBundle(),
        );

        // ...

        return $bundles;
    }

    // ...
}
```

## Configuration

You'll use the `pmg_elasticsearch` key in your `config.yml` file. The options
are very similar to what is done with with elasticsearch itself. Most of the
options here are `null` and use the default set by `Elasticsearch\Client`.

You can set up multiple connections:

```yaml
pmg_elasticsearch:
    default_client: example
    clients:
        example:
            connection_class: ~
            connection_factory_class: ~
            connection_pool_class: ~
            selector_class: ~
            serializer_class: ~
            sniff_on_start: ~
            hosts:
                - http://localhost:9200
        another: ~
```

Or just a single client:

```yaml
pmg_elasticsearch:
    connection_class: ~
    connection_factory_class: ~
    connection_pool_class: ~
    selector_class: ~
    serializer_class: ~
    sniff_on_start: ~
    hosts:
        - http://localhost:9200
```

## Services

The `pmg_elasticsearch.client` service will always be the default client. Other
clients can be accessed with `pmg_elasticsear.{name}.client`.

```php

use Symfony\Component\DependencyInjection\ContainerInterface;

/** @var $container ContainerInterface */
$client = $container->get('pmg_elasticsearch.client');

$otherClient = $container->get('pmg_elasticsearch.another.client');
```
