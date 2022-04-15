<?php

namespace Search\Controllers;

use Authorization\Authorization;
use Common\PageStandardController;
use MKrawczyk\FunQuery\FunQuery;

class Search extends PageStandardController
{
    public function index(string $query = "")
    {
        $results = (new \Search\Search())->searchAllGrouped($query, \Authorization\Authorization::getUserId(), 1000, function ($r) {
            return ($r->permission_group === null && $r->permission_name == null) || $this->can($r->permission_group, $r->permission_name);
        });
        $this->addView('Search', 'Search', ['query' => $query, 'results' => $results]);
    }

    public function openSearchDescription()
    {
        //header('content-type: application/opensearchdescription+xml;chasrset=UTF-8');
        header('content-type: application/xml;chasrset=UTF-8');
        echo (new \Search\Search())->generateOpenSearchDescription($this->getTitle())->saveXML();
        exit;
    }

    public function hasPermission(string $methodName)
    {
        return $methodName == 'openSearchDescription' || Authorization::isLogged();
    }
}