# Useage

## Custom Content Fetch Service

Filter, sort and fetch children only by defining services in yml:

```yaml
services:
    sf.mybundle.criterion.child.news:
        parent: sf.ezp_base.criterion.child
        calls:
            - [setChildContentTypeIdentifiers, [['news']]]

    sf.mybundle.sort_clause.news_date:
        parent: sf.ezp_base.sort_clause.field_value
        arguments:
            - 'news'   #content type identifier
            - 'start_date' # field identifier
        calls:
            - [setSortDirection, ['desc']]

    sf.mybundle.controller.fetch_children.news:
        parent: sf.ezp_base.controller.fetch_children
        calls:
            - [setCriterionGenerator, [@sf.mybundle.criterion.child.news]]
            - [setSortClauseGenerator, [@sf.mybundle.sort_clause.news_date]]
            - [setLimit, [10]]
```

More predefined stuff can be found in services.yml.


## Sujets
* Call the sujetcontroller from within your template
    `{{ render(controller('sf.standard.sujet.controller:renderSujetsAction', {
     locationId: location.id,
        template: 'components/sujet.html.twig'
        })) }}`

* add missing sujet alias to config.yml
```yaml
liip_imagine:
    filter_sets:
        sujet:
            filters:
                upscale: { min: [1920, 470] }
                thumbnail: { size: [1920, 470], mode: outbound }
```

## Search

Enable the routes of bundle in app/config/routing.yml:
```yaml
styleflashersearch:
    resource: "@StyleflashereZPlatformBaseBundle/Resources/config/routing.yml"
```

Now you can access the route "/search". Use "q" as GET parameter. By default the search controller uses the line view templates.

The templates can be changed by changing the searchresult_view parameter:
```yaml
system:
    site_group:
        search:
            searchresult_view: 'search'
```

## E-Mail obfuscation

This bundle used the twig filter of https://github.com/Propaganistas/Email-Obfuscator to obfuscate the e-mails with. This package doesn't exist anymore.
That's why EmailObfuscator.js is included in this package now.
```
'myadress@test.com'|obfuscateEmail
```
Therfore we provides overrides of the ezrichtext and ezemail fieldtypes. Enable them with:

```yaml
ezpublish:
    system:
        site_group:
            field_templates:
                -
                    template: "StyleflashereZPlatFormBaseBundle:fields:content_fields.html.twig"
                    # Priority is optional (default is 0). The higher it is, the higher your template gets in the list.
                    priority: 10

```
Import the required js from the assets folder.

```
<script src="/bundles/styleflasherezplatformbase/js/EmailObfuscator.js"></script>
```

## Youtube twig filter
```
{{'https://www.youtube.com/watch?v=myyoutubeidblaba'|youtube}}
```

## Contentblock

####Migration:
if you want to use the migration move the Contentblock migration from doc to src/MigrationsVersions
####Then run:
```
php bin/console kaliop:migration:migrate
```

Add this to view.yml
```yaml
imports:
   - { resource: "@StyleflashereZPlatformBaseBundle/Resources/config/views.yml" }
```
Add this to app.scss after importing all node modules
```scss
@import '../../../../vendor/styleflasher/ezplatformbasebundle/src/Resources/public/scss/contentblocks/main';
```
> use SCSS variables to override color and space, not CSS.....


## SEO
DEPRECATED: USE https://github.com/Novactive/NovaeZSEOBundle
~~Insert the following snippet into you <head> tag:~~

## Redirect of Contentblock to parent with a full view
Usage in views.yml:

```yaml
ezpublish:
    system:
        default:
            content_view:
                full:
                    contentblock_general:
                        controller: sf.standard.contentblock_redirect.controller:redirectToParentAction
                        params:
                            displayableContentTypeIdentifiers:
                                - identifier_of_contenttype_with_an_actual_full_view
                        match:
                            Identifier\ContentType:
                                - identifier_of_block_element
```
