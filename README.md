Elenyum Open API
==============================
* * *

This bundle generates documentation and returns JSON formatted according to the OpenAPI specification.


## Install
```shell
composer require elenyum/open-api
```

## Configuration
For adding configuration, create a config file in the project root directory, /config/packages/elenyum_open_api.yaml.
In the config file add:

```yaml
elenyum_open_api:
    documentation:
        info:
            title: My project name
            description: My project description
            version: 1.0.0
```

To add a route to get the json specification
add to project root file /config/routes/elenyum_open_api.yaml

width config example:
```yaml
app.openapi:
  path: /v1/doc
  methods: GET
  defaults: { _controller: elenyum_open_api }
```


### Cache Configuration

* * *

*   **cache.enable**:  
    Indicates whether caching is activated. Default: `false`.



*   **cache.item\_id**:  
    Sets the cache item identifier. Default: `elenyum_open_api`.






#### Example:

yaml
```yaml
cache:
  enable: true
  item_id: 'my_custom_cache_id'
```



### Documentation Configuration

* * *

Serves as a foundation for creating API documentation using attributes as keys.



*   **documentation.info.title**:  
    The title of the application in the documentation: `My App`.



*   **documentation.info.description**:  
    The description of the application in the documentation: `Description service`.



*   **documentation.info.version**:  
    The version of the application in the documentation: `1.0.0`.






#### Example:



yaml
```yaml
documentation:
  info:
    title: 'My App'
    description: 'Description service'
    version: '1.0.0'
```



### Other Configuration



* * *

Filters the routes that will be documented and manages their visibility in the generated documentation.

*   **path\_patterns**:  
    An array of regex patterns to match against the path for including routes.



*   **host\_patterns**:  
    An array of regex patterns to match against the host for including routes.



*   **name\_patterns**:  
    An array of regex patterns to match against the route name for including routes.



*   **with\_tag**:  
    A boolean indicating if routes should be filtered by tag (annotations).



*   **disable\_default\_routes**:  
    A boolean indicating if default routes without explicit OpenAPI annotations should be excluded.






#### Example



yaml
```yaml
path_patterns: ['^/api']
host_patterns: ['^api\.']
name_patterns: ['^api_v1']
with_tag: true
disable_default_routes: false
```
