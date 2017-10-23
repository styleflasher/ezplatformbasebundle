<?php

namespace Styleflasher\eZPlatformBaseBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Pagerfanta\Pagerfanta;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use eZ\Publish\Core\Pagination\Pagerfanta\LocationSearchAdapter;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    public function indexAction(Request $request)
    {
        $pager = null;
        $searchCount = 0;
        $repository = $this->container->get('ezpublish.api.repository');
        $configResolver = $this->container->get('ezpublish.config.resolver');

        $viewType = $configResolver->getParameter('search.searchresult_view', 'styleflashere_z_platform_base');

        $queryString= $request->query->get('q');

        $wildcard = $configResolver->getParameter('search.wildcard', 'styleflashere_z_platform_base');
        if ($wildcard === true) {
            $queryString .="*";
        }
        if ($queryString) {
            $searchService = $repository->getSearchService();
            $query = new Query();
            $query->filter = new Criterion\LogicalAnd(
                array(
                    new Criterion\FullText($queryString),
                    new Criterion\Visibility(Criterion\Visibility::VISIBLE),
                    new Criterion\LanguageCode(array('ger-DE'), true),
                )
            );

            $pager = new Pagerfanta(
                new ContentSearchAdapter($query, $searchService)
            );
            $limit = 10;
            $pager->setMaxPerPage($limit);
            $pager->setCurrentPage($request->get('page', 1));
            $searchCount = $pager->getNbResults();
        }

        return $this->render('StyleflashereZPlatformBaseBundle:search:search.html.twig', array(
            'q' => $queryString,
            'searchCount' => $searchCount,
            'viewType' => $viewType,
            'results' => $pager
        ));
    }
}
