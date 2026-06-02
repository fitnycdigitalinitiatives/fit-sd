<?php

namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;

class QueryToStringHelper extends AbstractHelper
{
    public function __invoke($query, $params, $searchForm)
    {
        $escape = $this->getView()->plugin('escapeHtml');
        $api = $this->getView()->plugin('api');
        $queryStringArray = [];
        if (array_key_exists('q', $query) && ($basicQuery = $query['q'])) {
            $queryLink = $this->removeQueryLink($basicQuery, $query, $params);
            array_push($queryStringArray, $queryLink);
        }

        if (array_key_exists('filters', $query) && array_key_exists('queries', $query['filters']) && ($queryFilters = $query['filters']['queries'])) {
            $availableSearchFields = $searchForm->getAvailableSearchFields();
            foreach ($queryFilters as $queryFilter) {
                if ($queryFilter['term']) {
                    $filterLink = $this->removeFilterLink($queryFilter, $availableSearchFields, $query, $params);
                    array_push($queryStringArray, $filterLink);
                }
            }
        }

        if (array_key_exists('date_range_start', $query) || array_key_exists('date_range_end', $query)) {
            $date_range_start = isset($query['date_range_start']) ? $query['date_range_start'] : '*';
            if (is_array($date_range_start)) {
                $date_range_start = array_filter($date_range_start);
                if (!$date_range_start) {
                    $date_range_start = '*';
                } else {
                    $date_range_start = $date_range_start[0];
                }
            }
            $date_range_end = isset($query['date_range_end']) ? $query['date_range_end'] : '*';
            if (is_array($date_range_end)) {
                $date_range_end = array_filter($date_range_end);
                if (!$date_range_end) {
                    $date_range_end = '*';
                } else {
                    $date_range_end = $date_range_end[0];
                }
            }
            if (($date_range_start != '*') || ($date_range_end != '*')) {
                $dateRangeLink = $this->removeDateRangeLink($date_range_start, $date_range_end, $query, $params);
                array_push($queryStringArray, $dateRangeLink);
            }
        }

        if (array_key_exists('item_set_id', $query) && ($collectionIDs = $query['item_set_id'])) {
            foreach ($collectionIDs as $collectionID) {
                if ($collectionID) {
                    $thisItemSet = $api->read('item_sets', $collectionID)->getContent();
                    $collectionTitle = $thisItemSet->displayTitle();
                    $collectionLink = $this->removeCollectionLink($collectionID, $collectionTitle, $query, $params);
                    array_push($queryStringArray, $collectionLink);
                }
            }
        }

        if (array_key_exists('resource_class_id', $query) && ($resourceClassIDs = $query['resource_class_id'])) {
            foreach ($resourceClassIDs as $resourceClassID) {
                if ($resourceClassID) {
                    $thisResourceClass = $api->read('resource_classes', $resourceClassID)->getContent();
                    $resourceLabel = $thisResourceClass->label();
                    $resourceClassLink = $this->removeResourceClassLink($resourceClassID, $resourceLabel, $query, $params);
                    array_push($queryStringArray, $resourceClassLink);
                }
            }
        }
        if (array_key_exists('limit', $query) && ($queryFacets = $query['limit'])) {
            $facetLinkHelper = $this->getView()->plugin('facetLink');
            foreach ($queryFacets as $queryFacetName => $queryFacetValues) {
                foreach ($queryFacetValues as $queryFacetValue) {
                    $facetLink = $this->removeFacetLink($query, $params, $queryFacetName, $queryFacetValue);
                    array_push($queryStringArray, $facetLink);
                }
            }
        }

        if ($queryStringArray) {
            $html = '<ul id="query" class="list-inline mb-0">';
            foreach ($queryStringArray as $key => $queryString) {
                $html .= '<li class="list-inline-item">' . $queryString . '</li>';
            }
            $html .= '</ul>';
            return $html;
        } else {
            return null;
        }
    }

    protected function removeQueryLink($basicQuery, $query, $params)
    {
        if (array_key_exists('suggester', $query) && ($query['suggester'] == 'true')) {
            unset($query['page']);
            if ($basicQuery) {
                $basicQuery = stripslashes($basicQuery);
                if ((substr($basicQuery, 0, 1) == '"') && (substr($basicQuery, -1, 1) == '"')) {
                    $basicQuery = substr(substr($basicQuery, 1), 0, -1);
                }
            }
        }
        if (array_key_exists('label', $query) && ($label = $query['label'])) {
            $basicQuery = $label;
            unset($query['label']);
        }
        $escape = $this->getView()->plugin('escapeHtml');
        unset($query['q']);
        unset($query['page']);
        $url = $this->getView()->url('site/search', $params, ['query' => $query]);
        return '<a href="' . $escape($url) . '" class="link-dark remove-query text-decoration-none"><svg xmlns="http://www.w3.org/2000/svg" aria-label="Remove search term:" class="bi bi-x-circle me-1" viewBox="0 0 16 16"><title>Remove search term:</title> <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/> <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>' . $escape($basicQuery) . '</a>';
    }

    protected function removeFilterLink($queryFilter, $availableSearchFields, $query, $params)
    {
        $escape = $this->getView()->plugin('escapeHtml');
        $label = "";
        foreach ($availableSearchFields as $searchField) {
            if ($searchField['name'] == $queryFilter['field']) {
                $label = $searchField['label'];
            }
        }
        $queryFilterString = strtolower($label) . ': ' . $queryFilter['term'];
        $values = [];
        foreach ($query['filters']['queries'] as $queryFilterMatch) {
            if ($queryFilterMatch != $queryFilter) {
                array_push($values, $queryFilterMatch);
            }
        }
        $query['filters']['queries'] = $values;
        unset($query['page']);
        $url = $this->getView()->url('site/search', $params, ['query' => $query]);
        return '<a href="' . $escape($url) . '" class="link-dark remove-query text-decoration-none"><svg xmlns="http://www.w3.org/2000/svg" aria-label="Remove search term:" class="bi bi-x-circle me-1" viewBox="0 0 16 16"><title>Remove search term:</title> <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/> <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>' . $escape($queryFilterString) . '</a>';
    }

    protected function removeDateRangeLink($date_range_start, $date_range_end, $query, $params)
    {
        $escape = $this->getView()->plugin('escapeHtml');
        //There should only ever be one date range, so it can just be set to a blank array
        $query['date_range_start'] = [""];
        $query['date_range_end'] = [""];
        unset($query['page']);
        $url = $this->getView()->url('site/search', $params, ['query' => $query]);
        $dateRangeString = $date_range_start . '-' . $date_range_end;
        return '<a href="' . $escape($url) . '" class="link-dark remove-query text-decoration-none"><svg xmlns="http://www.w3.org/2000/svg" aria-label="Remove search term:" class="bi bi-x-circle me-1" viewBox="0 0 16 16"><title>Remove search term:</title> <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/> <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>' . $escape($dateRangeString) . '</a>';
    }

    protected function removeCollectionLink($collectionID, $collectionTitle, $query, $params)
    {
        $escape = $this->getView()->plugin('escapeHtml');
        $values = [];
        foreach ($query['item_set_id'] as $collectionIDMatch) {
            if ($collectionIDMatch != $collectionID) {
                array_push($values, $collectionIDMatch);
            }
        }
        $query['item_set_id'] = $values;
        unset($query['page']);
        $url = $this->getView()->url('site/search', $params, ['query' => $query]);
        $collectionString = $collectionTitle;
        return '<a href="' . $escape($url) . '" class="link-dark remove-query text-decoration-none"><svg xmlns="http://www.w3.org/2000/svg" aria-label="Remove search term:" class="bi bi-x-circle me-1" viewBox="0 0 16 16"><title>Remove search term:</title> <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/> <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>' . $escape($collectionString) . '</a>';
    }

    protected function removeResourceClassLink($resourceClassID, $resourceLabel, $query, $params)
    {
        $escape = $this->getView()->plugin('escapeHtml');
        $values = [];
        foreach ($query['resource_class_id'] as $resourceClassIDMatch) {
            if ($resourceClassIDMatch != $resourceClassID) {
                array_push($values, $resourceClassIDMatch);
            }
        }
        $query['resource_class_id'] = $values;
        unset($query['page']);
        $url = $this->getView()->url('site/search', $params, ['query' => $query]);
        $resourceString = 'Resource class: ' . $resourceLabel;
        return '<a href="' . $escape($url) . '" class="link-dark remove-query text-decoration-none"><svg xmlns="http://www.w3.org/2000/svg" aria-label="Remove search term:" class="bi bi-x-circle me-1" viewBox="0 0 16 16"><title>Remove search term:</title> <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/> <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>' . $escape($resourceString) . '</a>';
    }


    protected function removeFacetLink($query, $params, $name, $facet)
    {
        $escape = $this->getView()->plugin('escapeHtml');
        $values = $query['limit'][$name];
        $values = array_filter($values, function ($v) use ($facet) {
            return $v != $facet;
        });
        $query['limit'][$name] = $values;
        unset($query['page']);
        $url = $this->getView()->url('site/search', $params, ['query' => $query]);
        if (($name == 'fitcore_colorpalette' || $name == 'fitcore_predominantcolor') && preg_match('/^#?([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $facet)) {
            if (!str_starts_with($facet, "#")) {
                $facet = '#' . $facet;
            }
            $facet = "<div class='color-swatch' style='width:1em;height:1em;background-color:$facet' aria-label='Color swatch of $facet' title='Color value: $facet'></div>";
        } else {
            $facet = $escape($facet);
        }
        return '<a href="' . $escape($url) . '" class="link-dark remove-query text-decoration-none"><svg xmlns="http://www.w3.org/2000/svg" aria-label="Remove facet:" class="bi bi-x-circle me-1" viewBox="0 0 16 16"><title>Remove facet:</title> <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/> <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>' . $facet . '</a>';
    }
}
