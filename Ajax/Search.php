<?php

namespace Search\Ajax;

use MKrawczyk\FunQuery\FunQuery;

class Search extends \Core\AjaxController
{
    public function searchAll(string $q)
    {
        $results = (new \Search\Search())->searchAll($q, \Authorization\Authorization::getUserId());
        return FunQuery::create($results)->filter(function ($r) {
            return ($r->permission_group === null && $r->permission_name == null) || $this->can($r->permission_group, $r->permission_name);
        });
    }
}