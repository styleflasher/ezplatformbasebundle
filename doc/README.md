# Useage

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

## SEO
DEPRECATED: USE https://github.com/Novactive/NovaeZSEOBundle
~~Insert the following snippet into you <head> tag:~~

