<?php

namespace Search;

use MKrawczyk\FunQuery\FunQuery;

class Actions implements ISearchable
{
    public function listAllActions()
    {
        $root = [];
        $modules = scandir(__DIR__ . '/../');
        foreach ($modules as $module) {
            if ($module == '.' || $module == '..') {
                continue;
            }
            $filename = __DIR__ . '/../' . $module . '/actions.xml';
            if (is_file($filename)) {
                $xml = simplexml_load_string(file_get_contents($filename));
                foreach ($xml->children() as $element) {
                    $root[] = $this->getAsStdClass($element);
                }
            }
        }
        return $root;
    }

    private function getAsStdClass(\SimpleXMLElement $element)
    {
        $ret = new \StdClass();
        foreach ($element->children() as $name => $value) {
            if ($name == 'action') {
                $ret->menu = [];
                foreach ($value->children() as $childElement) {
                    $ret->menu[] = $this->getAsStdclass($childElement);
                }
            } else if ($name == 'permission') {
                $ret->permission = new \stdClass();
                foreach ($value as $childName => $childElement) {
                    $ret->permission->$childName = $childElement->__toString();
                }
            } else
                $ret->$name = $value->__toString();
        }
        return $ret;
    }

    public function getAllElementsToSearch(): array
    {
        $allAction = $this->listAllActions();
        return FunQuery::create($allAction)->map(fn($x) => (object)[
            'content' => $x->keywords ?? '',
            'name' => $x->title ?? '',
            'link' => $x->link,
            'permission_group' => $x->permission->group ?? null,
            'permission_name' => $x->permission->name ?? null
        ])->toArray();
    }

    static function getSearchName(): string
    {
        return "Akcje";
    }
}