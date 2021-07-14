<?php
namespace Search;

interface ISearchable
{
    function getAllElementsToSearch(): array;
    static function getSearchName(): string;
}