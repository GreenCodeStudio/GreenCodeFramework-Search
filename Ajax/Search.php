<?php

namespace Search\Ajax;

use MKrawczyk\FunQuery\FunQuery;

class Search extends \Core\AjaxController
{
    public function searchAll(string $q)
    {
        return (new \Search\Search())->searchAll($q, \Authorization\Authorization::getUserId(), 20, function ($r) {
            return ($r->permission_group === null && $r->permission_name == null) || $this->can($r->permission_group, $r->permission_name);
        });
    }
}