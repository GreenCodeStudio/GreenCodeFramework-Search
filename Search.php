<?php

namespace Search;

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
            $object = new $className();
            $classItems = $object->getAllElementsToSearch();
            foreach ($classItems as $item) {
                $item->class = $className;
                $item->version = $version;
                $items[] = $item;
                foreach (explode($item->content, ' ') as $word)
                    $words[] = ['word' => $word, 'class'=>$className, 'version'=>$version, 'element_id'=>$item->element_id];
            }
        }
        $repository = new SearchRepository();
        $repository->replace($items,$words, $version);
    }

    function getISearchableClasses()
    {
        $files = $this->getAllRepositoryPhpFiles();
        foreach ($files as $file) {
            include_once $file;
        }
        $classes = get_declared_classes();
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
        $modules = scandir(__DIR__."/..");
        $ret = [];
        foreach ($modules as $module) {
            if ($module == '.' || $module == '..') continue;
            if (is_dir(__DIR__."/../".$module."/Repository"))
                $ret = array_merge($ret, $this->getAllPhpFiles(__DIR__."/../".$module."/Repository"));
        }

        return $ret;
    }

    function getAllPhpFiles($path)
    {
        $elements = scandir($path);
        $ret = [];
        foreach ($elements as $element) {
            if ($element == '.' || $element == '..') continue;
            if (is_dir($path.'/'.$element))
                $ret = array_merge($ret, $this->getAllPhpFiles($path.'/'.$element));
            else if (substr($element, -4) === ".php") {
                $ret[] = $path.'/'.$element;
            }
        }
        return $ret;
    }

    function searchAll(string $query, ?int $idUser)
    {
        return (new SearchRepository())->searchAll($query, $idUser);
    }
}