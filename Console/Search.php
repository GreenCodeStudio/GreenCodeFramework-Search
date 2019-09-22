<?php

namespace Search\Console;

use Core\AbstractController;
use stdClass;

class Search extends AbstractController
{

    function generateSearchIndex()
    {
        (new \Search\Search())->generateSearchIndex();
    }
}
