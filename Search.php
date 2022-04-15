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

    function searchAll(string $query, ?int $idUser, int $limit = 1000, callable $filter=null)
    {
        return FunQuery::create((new SearchRepository())->searchAll($query, $idUser, $limit))->filter($filter);
    }

    function searchAllGrouped(string $query, ?int $idUser, int $limit = 1000, callable $filter=null)
    {
        $results = $this->searchAll($query, $idUser, $limit, $filter);
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

    public function generateOpenSearchDescription($title)
    {
        $root=new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"/>');
        $root->ShortName=$title;
        $root->Description=$title;
        $root->InputEncoding='UTF-8';
        $root->Url='';
        $root->Url->addAttribute('type','text/html');
        $root->Url->addAttribute('method','get');
        $root->Url->addAttribute('template',"https://$_SERVER[HTTP_HOST]/Search/index/{searchTerms}");

        $root->Url[]='';
        $root->Url[1]->addAttribute('type','application/opensearchdescription+xml');
        $root->Url[1]->addAttribute('rel','self');
        $root->Url[1]->addAttribute('template',"https://$_SERVER[HTTP_HOST]/Search/openSearchDescription");
        $root->Image="https://$_SERVER[HTTP_HOST]/dist/Common/icon.png";
        $root->Image->addAttribute('width','512');
        $root->Image->addAttribute('height','512');
        $root->Image->addAttribute('type','image/png');
        return $root;
    }
}