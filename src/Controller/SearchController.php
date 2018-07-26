<?php

namespace Styleflasher\eZPlatformBaseBundle\Controller;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    public function indexAction(Request $request)
    {
        $pager = null;
        $searchCount = 0;
        $repository = $this->container->get('ezpublish.api.repository');
        $configResolver = $this->container->get('ezpublish.config.resolver');

        $rootLocationId = $configResolver->getParameter('content.tree_root.location_id');
        $rootLocation = $repository->getLocationService()->loadLocation($rootLocationId);

        $viewType = $configResolver->getParameter('search.searchresult_view', 'styleflashere_z_platform_base');

        $queryString = $request->query->get('q');
        $searchString = $queryString;

        $wildcard = $configResolver->getParameter('search.wildcard', 'styleflashere_z_platform_base');
        $limit = $configResolver->getParameter('search.limit', 'styleflashere_z_platform_base');
        if ($wildcard === true) {
            $searchString .="*";
        }
        if ($searchString) {
            $searchService = $repository->getSearchService();
            $query = new Query();
            $query->filter = new Criterion\LogicalAnd(
                array(
                    new Criterion\Subtree($rootLocation->pathString),
                    new Criterion\FullText($searchString),
                    new Criterion\Visibility(Criterion\Visibility::VISIBLE),
                    new Criterion\LanguageCode(array('ger-DE'), true),
                )
            );

            $pager = new Pagerfanta(
                new ContentSearchAdapter($query, $searchService)
            );
            $pager->setMaxPerPage($limit);
            $pager->setCurrentPage($request->get('page', 1));
            $searchCount = $pager->getNbResults();
        }

        return $this->render(
            'StyleflashereZPlatformBaseBundle:search:search.html.twig',
            [
                'q' => $queryString,
                'searchCount' => $searchCount,
                'viewType' => $viewType,
                'results' => $pager
            ]
        );
    }
}
