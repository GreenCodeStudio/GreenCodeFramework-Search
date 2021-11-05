<?php

namespace Search;

use Core\Log;
use MKrawczyk\FunQuery\FunQuery;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use Search\Repository\SearchRepository;

class Search
{
    public function generateSearchIndex()
    {
        $items = [];
        $words = [];
        $version = uniqid();
        $classes = $this->getISearchableClasses();
        foreach ($classes as $className) {
            try {
                $object = new $className();
                $classItems = $object->getAllElementsToSearch();
                foreach ($classItems as $item) {
                    $item->uuid = Uuid::uuid4();
                    $item->class = $className;
                    $item->version = $version;
                    $items[] = $item;
                    foreach (explode(' ', $item->content) as $word)
                        $words[] = ['word' => substr($word, 0, 32), 'uuid_search' => $item->uuid];
                }
            }catch(\Throwable $ex){
                Log::Exception($ex);
            }
        }
        $repository = new SearchRepository();
        $repository->replace($items, $words, $version);
    }

    function getISearchableClasses()
    {
        include_once __DIR__.'/Actions.php';
        $files = $this->getAllRepositoryPhpFiles();
        foreach ($files as $file) {
            include_once $file;
        }
        $classes = get_declared_classes();
        dump($classes);
        $implementsISearchable = [];
        foreach ($classes as $className) {
            $reflect = new ReflectionClass($className);
            if ($reflect->implementsInterface('\Search\ISearchable'))
                $implementsISearchable[] = $className;
        }
        return $implementsISearchable;
    }

    function getAllRepositoryPhpFiles()
    {
        $modules = scandir(__DIR__ . "/..");
        $ret = [];
        foreach ($modules as $module) {
            if ($module == '.' || $module == '..') continue;
            if (is_dir(__DIR__ . "/../" . $module . "/Repository"))
                $ret = array_merge($ret, $this->getAllPhpFiles(__DIR__ . "/../" . $module . "/Repository"));
        }

        return $ret;
    }

    function getAllPhpFiles($path)
    {
        $elements = scandir($path);
        $ret = [];
        foreach ($elements as $element) {
            if ($element == '.' || $element == '..') continue;
            if (is_dir($path . '/' . $element))
                $ret = array_merge($ret, $this->getAllPhpFiles($path . '/' . $element));
            else if (substr($element, -4) === ".php") {
                $ret[] = $path . '/' . $element;
            }
        }
        return $ret;
    }

    function searchAll(string $query, ?int $idUser, int $limit = 1000, callable $filter)
    {
        return FunQuery::create((new SearchRepository())->searchAll($query, $idUser, $limit))->filter($filter);
    }

    function searchAllGrouped(string $query, ?int $idUser, int $limit = 1000, callable $filter)
    {
        $results = $this->searchAll($query, $idUser, $limit = 1000, $filter);
        $resultsGrouped = [];
        foreach ($results as $result) {
            $resultsGrouped[$result->class][] = $result;
        }
        $ret = [];
        foreach ($resultsGrouped as $className => $group) {
            try {
                $name = $className::getSearchName();
            } catch (\Throwable $ex) {
                $name = '';
            }
            $ret[] = (object)['name' => $name, 'items' => $group];
        }
        return $ret;
    }
}