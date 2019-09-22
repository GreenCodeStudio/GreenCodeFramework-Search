<?php

namespace Search;

use ReflectionClass;
use World\Repository\SearchRepository;

class Search
{
    public function generateSearchIndex()
    {
        $items = [];
        $version = uniqid();
        $classes = $this->getISearchableClasses();
        foreach ($classes as $className) {
            $object = new $className();
            $classItems = $object->getAllElementsToSearch();
            foreach ($classItems as $item) {
                $item->class = $className;
                $item->version = $version;
                $items[] = $item;
            }
        }
        $repository = new SearchRepository();
        $repository->replace($items, $version);
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
}