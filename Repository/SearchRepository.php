<?php

namespace World\Repository;

use Core\DB;


class SearchRepository extends \Core\Repository
{

    public function __construct()
    {
        $this->archiveMode = static::ArchiveMode_OnlyExisting;
    }

    public function defaultTable(): string
    {
        return 'search';
    }

    public function replace($data, $version)
    {
        DB::insertMultiple('search', $data);
        DB::query("DELETE FROM search WHERE version != ?", [$version]);
    }

}