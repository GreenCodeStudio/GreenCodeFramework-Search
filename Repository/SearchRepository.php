<?php

namespace Search\Repository;

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

    public function replace($data,$words, $version)
    {
        DB::insertMultiple('search', $data);
        DB::insertMultiple('search_word', $words);
        DB::query("DELETE FROM search WHERE version != ?", [$version]);
    }

    public function searchAll(string $query, ?int $idUser)
    {
        return DB::get("SELECT class, name, element_id, link, permission_group, permission_name
                                FROM search
                                WHERE MATCH (content) AGAINST (? IN NATURAL LANGUAGE MODE) AND (id_user is null OR id_user = ?)", [$query, $idUser]);
    }

}