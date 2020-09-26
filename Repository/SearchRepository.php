<?php

namespace Search\Repository;

use Core\Database\DB;
use MKrawczyk\FunQuery\FunQuery;


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

    public function replace($data, $words, $version)
    {
        DB::insertMultiple('search', $data);
        DB::insertMultiple('search_word', $words);
        DB::query("DELETE FROM search_word WHERE (SELECT version FROM search WHERE search.uuid = search_word.uuid_search) != ?", [$version]);
        DB::query("DELETE FROM search WHERE version != ?", [$version]);
    }

    public function searchAll(string $query, ?int $idUser, int $limit=1000)
    {
        list($joinSql, $parameters) = $this->generateSearchSql($query);
        $parameters[] = $idUser;
        return DB::get("SELECT class, name, element_id, link, permission_group, permission_name
                                FROM search
                                $joinSql
                                WHERE id_user is null OR id_user = ?
                                LIMIT $limit", $parameters);
    }

    private function generateSearchSql($query)
    {
        $words = FunQuery::create(explode(' ', $query))->filter(fn($w) => $w !== '');
        $i = 0;
        $joins = [];
        $parameters = [];
        foreach ($words as $word) {
            $i++;
            $joins[] = "JOIN search_word sw$i ON sw$i.uuid_search = search.uuid AND sw$i.word LIKE ? ";
            $parameters[] = $word."%";
        }
        return [implode("\r\n", $joins), $parameters];
    }

    public function searchClass(string $className, string $query)
    {
        list($joinSql, $parameters) = $this->generateSearchSql($query);
        $parameters[] = $className;
        $searchedIdObjects = DB::get("SELECT element_id
                                FROM search
                                $joinSql
                                WHERE class = ?
                                ", $parameters);

        return array_map(fn($x) => $x->element_id, $searchedIdObjects);
    }

}