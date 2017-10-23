# Useage

## Custom Content Fetch Service

Filter, sort and fetch children only by defining services in yml:

`
# Example Criteria
services:
    # example child criterion for 'news' class
    sf.mybundle.criterion.child.news:
        parent: sf.ezp_base.criterion.child
        calls:
            - [setChildContentTypeIdentifiers, [['news']]]

    # Example sort Clause Generators
    sf.mybundle.sort_clause.news_date:
        parent: sf.ezp_base.sort_clause.field_value
        arguments:
            - 'news'   #content type identifier
            - 'start_date' # field identifier
        calls:
            - [setSortDirection, ['desc']]

    # Example controller with custom criterion and sort clause generatorss
    sf.mybundle.controller.fetch_children.news:
        parent: sf.ezp_base.controller.fetch_children
        calls:
            - [setCriterionGenerator, [@sf.mybundle.criterion.child.news]]
            - [setSortClauseGenerator, [@sf.mybundle.sort_clause.news_date]]
            - [setLimit, [10]]

`

More predefined stuff can be found in services.yml.


## Sujets
* Call the sujetcontroller from within your template
    `{{ render(controller('sf.standard.sujet.controller:renderSujetsAction', {
     locationId: location.id,
        template: 'components/sujet.html.twig'
        })) }}`

* add missing sujet alias to config.yml
    `
liip_imagine:
    filter_sets:
        sujet:
            filters:
                upscale: { min: [1920, 470] }
                thumbnail: { size: [1920, 470], mode: outbound }
`

## Search

Enable the routes of bundle in app/config/routing.yml:
`
styleflashersearch:
    resource: "@StyleflashereZPlatformBaseBundle/Resources/config/routing.yml"
`

Now you can access the route "/search". Use "q" as GET parameter. By default the search controller uses the line view templates.

The templates can be changed by changing the searchresult_view parameter:
`
system:
    site_group:
        search:
            searchresult_view: 'search'
`

## E-Mail obfuscation

This bundle uses the twig filter of https://github.com/Propaganistas/Email-Obfuscator to obfuscate the e-mails with 
`
'myadress@test.com'|obfuscateEmail
`
Therfore we provides a override of the ezrichtext fieldtype. Enable it with:

Load the javascript fron the cdn or import it from the assets folder.

https://cdn.rawgit.com/Propaganistas/Email-Obfuscator/master/assets/EmailObfuscator.min.js

`
ezpublish:
    system:
        site_group:
            field_templates:
                -
                    template: "StyleflashereZPlatFormBaseBundle:fields:ezrichtext.html.twig"
                    # Priority is optional (default is 0). The higher it is, the higher your template gets in the list.
                    priority: 10
`

## Youtube twig filter
`
{{'https://www.youtube.com/watch?v=myyoutubeidblaba'|youtube}}
`

The filter returns the full youtube url that can be used in an iframe.

## SEO
DEPRECATED: USE https://github.com/Novactive/NovaeZSEOBundle
~~Insert the following snippet into you <head> tag:~~
