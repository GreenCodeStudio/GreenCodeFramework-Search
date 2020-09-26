<?php

namespace Search\Controllers;

use Common\PageStandardController;
use MKrawczyk\FunQuery\FunQuery;

class Search extends PageStandardController
{
    public function index(string $query = "")
    {
        $results = (new \Search\Search())->searchAll($query, \Authorization\Authorization::getUserId());
        $results = FunQuery::create($results)->filter(function ($r) {
            return ($r->permission_group === null && $r->permission_name == null) || $this->can($r->permission_group, $r->permission_name);
        });
        $this->addView('Search', 'Search', ['query' => $query, 'results' => $results]);
    }
}